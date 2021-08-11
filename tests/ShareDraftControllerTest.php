<?php

namespace SilverStripe\ShareDraftContent\Tests;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\Flysystem\FlysystemAssetStore;
use SilverStripe\Assets\InterventionBackend;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Assets\Image;
use SilverStripe\Security\Member;
use SilverStripe\ShareDraftContent\Controllers\ShareDraftController;
use SilverStripe\ShareDraftContent\Extensions\ShareDraftContentSiteTreeExtension;

class ShareDraftControllerTest extends SapphireTest
{
    protected static $fixture_file = 'ShareDraftControllerTest.yml';

    public function setUp()
    {
        // This was copy and adapted from ImageTest::setup()
        parent::setUp();
        TestAssetStore::activate('ShareDraftControllerTest');
        foreach (Image::get() as $image) {
            $sourcePath = __DIR__ . '/ShareDraftControllerTest/' . $image->Name;
            $image->setFromLocalFile($sourcePath, $image->Filename);
        }
        InterventionBackend::config()->set('error_cache_ttl', [
            InterventionBackend::FAILED_INVALID => 0,
            InterventionBackend::FAILED_MISSING => '5,10',
            InterventionBackend::FAILED_UNKNOWN => 300,
        ]);
    }

    public function tearDown()
    {
        TestAssetStore::reset();
        parent::tearDown();
    }

    public function testPreviewGrantsAccess()
    {
        /** @var SiteTree|ShareDraftContentSiteTreeExtension $page */
        $page = $this->objFromFixture(SiteTree::class, 'x1');
        $noPermissionsImage = $this->objFromFixture(Image::class, 'no-permissions');
        $permissionsImage = $this->objFromFixture(Image::class, 'permissions');
        $superUser = $this->objFromFixture(Member::class, 'super-user');
        $contentAuthor = $this->objFromFixture(Member::class, 'content-author');

        // Assert fixture permissions are setup correctly
        $this->logInAs($superUser);
        $this->assertTrue($noPermissionsImage->canView());
        $this->assertTrue($permissionsImage->canView());
        $this->logOut();
        $this->logInAs($contentAuthor);
        $this->assertTrue($noPermissionsImage->canView());
        $this->assertFalse($permissionsImage->canView());
        $this->logOut();
        $this->assertFalse($noPermissionsImage->canView());
        $this->assertFalse($permissionsImage->canView());

        $store = Injector::inst()->get(AssetStore::class);
        $session = Controller::curr()->getRequest()->getSession();
        $noPermissionsFileID = $store->getFileID($noPermissionsImage->FileFilename, $noPermissionsImage->FileHash);
        $permissionsFileID = $store->getFileID($permissionsImage->FileFilename, $permissionsImage->FileHash);

        // http://localhost/preview/9ee5dec9603700f1/14c52cb1eec7b1f0
        preg_match('#/preview/(.+?)/(.+$)#', $page->ShareTokenLink(), $matches);
        $url = sprintf('/preview/%s/%s', $matches[1], $matches[2]);
        $request = new HTTPRequest('GET', $url);
        $request->setRouteParams(['Key' => $matches[1], 'Token' => $matches[2]]);
        $controller = new ShareDraftController();
        $key = FlysystemAssetStore::GRANTS_SESSION;

        // Assert super user
        $this->logInAs($superUser);
        $granted = $session->get($key);
        $this->assertNull($granted);
        $controller->preview($request);
        $granted = $session->get($key);
        $this->assertArrayHasKey($noPermissionsFileID, $granted);
        $this->assertArrayHasKey($permissionsFileID, $granted);
        $this->logOut();

        // Assert content author
        $this->logInAs($contentAuthor);
        $granted = $session->get($key);
        $this->assertNull($granted);
        $controller->preview($request);
        $granted = $session->get($key);
        $this->assertArrayHasKey($noPermissionsFileID, $granted);
        $this->assertArrayNotHasKey($permissionsFileID, $granted);
        $this->logOut();

        // Assert not-logged-in user
        $granted = $session->get($key);
        $this->assertNull($granted);
        $controller->preview($request);
        $granted = $session->get($key);
        $this->assertArrayHasKey($noPermissionsFileID, $granted);
        $this->assertArrayNotHasKey($permissionsFileID, $granted);
    }
}
