<?php

namespace SilverStripe\ShareDraftContent\Controllers;

use BadMethodCallException;
use PageController;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\Deprecation;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPRequestBuilder;
use SilverStripe\Control\HTTPResponse;
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
    private static $allowed_actions = [
        'preview'
    ];

    /**
     * @var array
     */
    private static $url_handlers = [
        '$Key/$Token' => 'preview'
    ];

    /**
     * @var bool
     */
    protected static $isViewingPreview = false;

    /**
     * @return bool
     */
    public static function getIsViewingPreview(): bool
    {
        return static::$isViewingPreview;
    }

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

        $this->extend('updatePage', $page);

        $latest = Versioned::get_latest_version(SiteTree::class, $shareToken->PageID);

        if (!$shareToken->isExpired() && $page->generateKey($shareToken->Token) === $key) {
            Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/bundle-frontend.css');

            // Temporarily un-secure the draft site and switch to draft
            $oldSecured = $this->getIsDraftSecured($session);
            $oldMode = Versioned::get_default_reading_mode();
            static::$isViewingPreview = true;

            // Process page inside an unsecured draft container
            try {
                $this->setIsDraftSecured($session, false);
                Versioned::set_default_reading_mode('Stage.Stage');

                $rendered = $this->getRenderedPageByURL($page->Link());

                // Render draft heading
                $data = new ArrayData(array(
                    'Page' => $page,
                    'Latest' => $latest,
                ));
                $include = (string) $data->renderWith('Includes/TopBar');
            } finally {
                $this->setIsDraftSecured($session, $oldSecured);
                // Use set_default_reading_mode() instead of set_reading_mode() because that's
                // what's used in Versioned::choose_site_stage()
                Versioned::set_default_reading_mode($oldMode);
                static::$isViewingPreview = false;
            }

            return str_replace('</body>', $include . '</body>', (string) $rendered->getBody());
        } else {
            return $this->errorPage();
        }
    }

    /**
     * @param string $url
     *
     * @deprecated 3.0 Use {@link getRenderedPageByURL()}
     *
     * @return HTTPResponse
     */
    protected function getRenderedPageByURLSegment($url)
    {
        Deprecation::notice(
            '3.0',
            'getRenderedPageByURLSegment() should be replaced with getRenderedPageByURL()'
        );

        $pageRequest = HTTPRequestBuilder::createFromEnvironment();
        $pageRequest->setHttpMethod('GET');
        $pageRequest->setUrl($url);

        $response = Director::singleton()->handleRequest($pageRequest);

        if ($response->isRedirect()) {
            // The redirect will probably be Absolute URL so just want the path
            $newUrl = parse_url($response->getHeader('location') ?? '', PHP_URL_PATH);

            return $this->getRenderedPageByURLSegment($newUrl);
        }

        return $response;
    }

    /**
     * @param string $url
     *
     * @return HTTPResponse
     */
    private function getRenderedPageByURL(string $url): HTTPResponse
    {
        // Clean and update live global variables. This is how
        // HTTPRequestBuilder::createFromEnvironment works internally.
        $variables = HTTPRequestBuilder::cleanEnvironment(Environment::getVariables());
        $variables['_SERVER']['REQUEST_URI'] = $url;
        $variables['_SERVER']['REQUEST_METHOD'] = 'GET';
        $variables['_SERVER']['HTTP_USER_AGENT'] = 'CLI';

        Environment::setVariables($variables);

        // Health-check prior to creating environment
        $pageRequest = HTTPRequestBuilder::createFromVariables($variables, @file_get_contents('php://input'));

        $response = Director::singleton()->handleRequest($pageRequest);

        if ($response->isRedirect()) {
            // The redirect will probably be Absolute URL so just want the path
            $newUrl = parse_url($response->getHeader('location') ?? '', PHP_URL_PATH);

            return $this->getRenderedPageByURL($newUrl);
        }

        return $response;
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
