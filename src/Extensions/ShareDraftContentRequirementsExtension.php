<?php

namespace SilverStripe\ShareDraftContent\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;

class ShareDraftContentRequirementsExtension extends DataExtension
{
    public function init()
    {
        Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/share-component.css');
        Requirements::javascript('silverstripe/sharedraftcontent: client/dist/js/main.js');
    }
}
