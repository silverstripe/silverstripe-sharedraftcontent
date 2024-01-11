<?php

namespace SilverStripe\ShareDraftContent\Extensions;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;

/**
 * @extends DataExtension<CMSMain>
 */
class ShareDraftContentRequirementsExtension extends DataExtension
{
    public function init()
    {
        Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/bundle-cms.css');
        Requirements::javascript('silverstripe/sharedraftcontent: client/dist/js/bundle.js');
    }
}
