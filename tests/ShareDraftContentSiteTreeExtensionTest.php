<?php

namespace SilverStripe\ShareDraftContent\Tests;

use Page;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ShareDraftContent\Controllers\ShareDraftController;
use SilverStripe\ShareDraftContent\Extensions\ShareDraftContentSiteTreeExtension;

class ShareDraftContentSiteTreeExtensionTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'ShareDraftContentSiteTreeExtensionTest.yml';

    public function testShareTokenLink()
    {
        /**
         * First we check if both pages generate new ShareTokenSalt values. Then we check that
         * these values are not the same.
         */

        /** @var Page|ShareDraftContentSiteTreeExtension $firstSharedPage */
        $firstSharedPage = $this->objFromFixture(Page::class, 'FirstSharedPage');

        $firstShareLink = $firstSharedPage->ShareTokenLink();

        $this->assertNotEmpty($firstSharedPage->ShareTokenSalt);

        /** @var Page|ShareDraftContentSiteTreeExtension $secondSharedPage */
        $secondSharedPage = $this->objFromFixture(Page::class, 'SecondSharedPage');

        $secondShareLink = $secondSharedPage->ShareTokenLink();

        $this->assertNotEmpty($secondSharedPage->ShareTokenSalt);

        $this->assertNotEquals($firstShareLink, $secondShareLink);

        // Ensure that salt between two pages causes key to differ for null token
        $this->assertNotEquals(
            $firstSharedPage->generateKey(null),
            $secondSharedPage->generateKey(null)
        );

        /**
         * Then we get the underlying token and send a preview request. With a valid key and token,
         * this will return a draft page. With an invalid key or token, this will return an expired
         * link page.
         */

        $firstSharedPageToken = $firstSharedPage->ShareTokens()->first();

        $this->assertNotEmpty($firstSharedPageToken);

        // Ensure that salt between two pages causes key to differ for either token
        $this->assertNotEquals(
            $firstSharedPage->generateKey($firstSharedPageToken->Token),
            $secondSharedPage->generateKey($firstSharedPageToken->Token)
        );

        $parts = explode('/', $firstShareLink);

        $token = array_pop($parts);
        $key = array_pop($parts);

        $this->assertEquals($token, $firstSharedPageToken->Token);

        $request = new HTTPRequest('GET', $firstShareLink);

        $request->setRouteParams(array(
            'Token' => $token,
            'Key' => $key,
        ));

        $controller = new ShareDraftController();
        $response = $controller->preview($request);

        $this->assertContains('share-draft-content-message', $response);

        $request = new HTTPRequest('GET', $firstShareLink);

        $request->setRouteParams(array(
            'Token' => $token,
            'Key' => '',
        ));

        $controller = new ShareDraftController();
        $response = $controller->preview($request);

        $this->assertContains('share-draft-error-page', $response);
    }
}
