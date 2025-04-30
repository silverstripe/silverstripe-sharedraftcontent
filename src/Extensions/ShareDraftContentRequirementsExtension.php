<?php

namespace SilverStripe\ShareDraftContent\Extensions;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Dev\Deprecation;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;

/**
 * @extends DataExtension<CMSMain>
 * @deprecated 5.3.0 Will be replaced with YAML configuration in a future major release
 */
class ShareDraftContentRequirementsExtension extends DataExtension
{
    public function __construct()
    {
        Deprecation::withSuppressedNotice(
            fn () => Deprecation::notice(
                '5.3.0',
                'Will be replaced with YAML configuration in a future major release',
                Deprecation::SCOPE_CLASS
            )
        );
        parent::__construct();
    }

    public function init()
    {
        Requirements::css('silverstripe/sharedraftcontent: client/dist/styles/bundle-cms.css');
        Requirements::javascript('silverstripe/sharedraftcontent: client/dist/js/bundle.js');
    }
}
