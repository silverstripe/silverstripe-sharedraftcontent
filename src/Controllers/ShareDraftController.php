<?php

namespace SilverStripe\ShareDraftContent\Controllers;

use BadMethodCallException;
use PageController;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ShareDraftContent\Extensions\ShareDraftContentSiteTreeExtension;
use SilverStripe\ShareDraftContent\Models\ShareToken;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

class ShareDraftController extends Controller
{
    /**
     * Controller for rendering draft pages.
     *
     * @config
     *
     * @var string
     */
    private static $controller = PageController::class;

    /**
     * @var array
     */
    private static $allowed_actions = array(
        'preview'
    );

    /**
     * @var array
     */
    private static $url_handlers = array(
        '$Key/$Token' => 'preview'
    );

    /**
     * @param HTTPRequest $request
     *
     * @return string|DBHTMLText
     */
    public function preview(HTTPRequest $request)
    {
        // Ensure this URL doesn't get picked up by HTTP caches
        HTTPCacheControlMiddleware::singleton()->disableCache();

        $key = $request->param('Key');
        $token = $request->param('Token');
        try {
            $session = $this->getRequest()->getSession();
        } catch (BadMethodCallException $e) {
            // Create a new session
            $session = $this->getRequest()
                ->setSession(Injector::inst()->create(Session::class, []))
                ->getSession();
        }
        /** @var ShareToken $shareToken */
        $shareToken = ShareToken::get()->filter('Token', $token)->first();

        if (!$shareToken) {
            return $this->errorPage();
        }

        /** @var SiteTree|ShareDraftContentSiteTreeExtension $page */
        $page = Versioned::get_by_stage(SiteTree::class, Versioned::DRAFT)
            ->byID($shareToken->PageID);

        $latest = Versioned::get_latest_version(SiteTree::class, $shareToken->PageID);

        $controller = $this->getControllerFor($page);

        if (!$shareToken->isExpired() && $page->generateKey($shareToken->Token) === $key) {
            Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/bundle-frontend.css');

            // Temporarily un-secure the draft site and switch to draft
            $oldSecured = $this->getIsDraftSecured($session);
            $oldMode = Versioned::get_reading_mode();

            // Process page inside an unsecured draft container
            try {
                $this->setIsDraftSecured($session, false);
                Versioned::set_stage('Stage');

                // Hack to get around ContentController::init() redirecting on home page
                $_FILES = array(array());

                // Create mock request; Simplify request to single top level request
                $pageRequest = new HTTPRequest('GET', $page->URLSegment);
                $pageRequest->match('$URLSegment//$Action/$ID/$OtherID', true);
                $pageRequest->setSession($session);
                $rendered = $controller->handleRequest($pageRequest);

                // Render draft heading
                $data = new ArrayData(array(
                    'Page' => $page,
                    'Latest' => $latest,
                ));
                $include = (string) $data->renderWith('Includes/TopBar');
            } finally {
                $this->setIsDraftSecured($session, $oldSecured);
                Versioned::set_reading_mode($oldMode);
            }

            return str_replace('</body>', $include . '</body>', (string) $rendered->getBody());
        } else {
            return $this->errorPage();
        }
    }

    /**
     * @return DBHTMLText
     */
    protected function errorPage()
    {
        Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/bundle-frontend.css');
        return $this->renderWith('ShareDraftContentError');
    }

    /**
     * @param SiteTree $page
     * @return ContentController
     */
    protected function getControllerFor($page)
    {
        return ModelAsController::controller_for($page);
    }

    /**
     * Check if the draft site is secured
     *
     * @param Session $session
     * @return bool True if the draft site is secured
     */
    protected function getIsDraftSecured(Session $session)
    {
        // Versioned >=1.2
        if (method_exists(Versioned::class, 'get_draft_site_secured')) {
            return Versioned::get_draft_site_secured();
        }

        // Fall back to session
        return !$session->get('unsecuredDraftSite');
    }

    /**
     * Set draft site security
     *
     * @param Session $session
     * @param bool $secured True if draft site should be secured
     */
    protected function setIsDraftSecured(Session $session, $secured)
    {
        // Versioned >=1.2
        if (method_exists(Versioned::class, 'set_draft_site_secured')) {
            Versioned::set_draft_site_secured($secured);
        }

        // Set session variable anyway
        $session->set('unsecuredDraftSite', !$secured);
    }
}
