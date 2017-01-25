<?php

namespace SilverStripe\ShareDraftContent\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;

class ShareDraftContentRequirementsExtension extends DataExtension
{
    /**
     * @todo Once CMSMain::init is protected, update visibility
     *
     * {@inheritDoc}
     */
    public function init()
    {
        Requirements::css(SHAREDRAFTCONTENT_DIR . '/css/share-component.css');
        Requirements::javascript(SHAREDRAFTCONTENT_DIR . '/javascript/main.js');
    }
}
