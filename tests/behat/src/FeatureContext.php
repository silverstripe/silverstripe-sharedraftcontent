<?php

namespace SilverStripe\ShareDraftContent\Tests\Behat\Context;

use SilverStripe\BehatExtension\Context\SilverStripeContext;

class FeatureContext extends SilverStripeContext
{
    private const KEY = 'behat.sharedraftcontent.link';

    /**
     * @Given /^I save the link to share draft content local storage$/
     */
    public function iSaveTheLinkToShareDraftContentLocalStorage()
    {
        $key = self::KEY;
        $js = <<<JS
            window.localStorage.setItem('{$key}', document.querySelector('.share-draft-content__link').value);
JS;
        $this->getSession()->evaluateScript($js);
    }

    /**
     * @Given /^I follow the link in share draft content local storage$/
     */
    public function iFollowTheLinkInShareDraftContentLocalStorage()
    {
        $key = self::KEY;
        $js = <<<JS
            window.location = window.localStorage.getItem('{$key}');
JS;
        $this->getSession()->evaluateScript($js);
    }

    /**
     * @Given /^I clear the link from share draft content local storage$/
     */
    public function iClearTheLinkFromShareDraftContentLocalStorage()
    {
        $key = self::KEY;
        $js = <<<JS
            window.localStorage.removeItem('{$key}');
JS;
        $this->getSession()->evaluateScript($js);
    }
}
