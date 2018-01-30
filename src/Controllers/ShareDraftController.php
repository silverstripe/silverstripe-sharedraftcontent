<?php

namespace SilverStripe\ShareDraftContent\Controllers;

use BadMethodCallException;
use Exception;
use PageController;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
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
     * @return string|HTMLText
     */
    public function preview(HTTPRequest $request)
    {
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
        /**
         * @var ShareToken $shareToken
         */
        $shareToken = ShareToken::get()->filter('Token', $token)->first();

        if (!$shareToken) {
            return $this->errorPage();
        }

        $page = Versioned::get_one_by_stage(
            SiteTree::class,
            'Stage',
            sprintf('"SiteTree"."ID" = \'%d\'', $shareToken->PageID)
        );

        $latest = Versioned::get_latest_version(SiteTree::class, $shareToken->PageID);

        $controller = $this->getControllerFor($page);

        if (!$shareToken->isExpired() && $page->generateKey($shareToken->Token) === $key) {
            Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/top-bar.css');

            // Temporarily un-secure the draft site and switch to draft
            $oldSecured = $session->get('unsecuredDraftSite');
            $oldMode = Versioned::get_reading_mode();
            $restore = function () use ($oldSecured, $oldMode, $session) {
                $session->set('unsecuredDraftSite', $oldSecured);
                Versioned::set_reading_mode($oldMode);
            };

            // Process page inside an unsecured draft container
            try {
                $session->set('unsecuredDraftSite', true);
                Versioned::set_stage('Stage');

                // Hack to get around ContentController::init() redirecting on home page
                $_FILES = array(array());

                // Create mock request; Simplify request to single top level request
                $pageRequest = new HTTPRequest('GET', $page->URLSegment);
                $pageRequest->match('$URLSegment//$Action/$ID/$OtherID', true);
                $pageRequest->setSession($session);
                $rendered = $controller->handleRequest($pageRequest, $this->model);

                // Render draft heading
                $data = new ArrayData(array(
                    'Page' => $page,
                    'Latest' => $latest,
                ));
                $include = (string) $data->renderWith('Includes/TopBar');
            } catch (Exception $ex) {
                $restore();
                throw $ex;
            }
            $restore();

            return str_replace('</body>', $include . '</body>', (string) $rendered->getBody());
        } else {
            return $this->errorPage();
        }
    }

    /**
     * @return HTMLText
     */
    protected function errorPage()
    {
        Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/error-page.css');

        return $this->renderWith('ShareDraftContentError');
    }

    /**
     * @param mixed $page
     *
     * @return mixed
     */
    protected function getControllerFor($page)
    {
        return ModelAsController::controller_for($page);
    }
}
