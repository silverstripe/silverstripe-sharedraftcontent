<?php

namespace SilverStripe\ShareDraftContent\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Security\Security;

class ShareDraftContentControllerExtension extends Extension
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'MakeShareDraftLink',
    ];

    /**
     * @return mixed
     */
    public function MakeShareDraftLink()
    {
        if ($member = Security::getCurrentUser()) {
            if ($this->owner->hasMethod('CurrentPage') && $this->owner->CurrentPage()->canView($member)) {
                return $this->owner->CurrentPage()->ShareTokenLink();
            }
            if ($this->owner->hasMethod('canView') && $this->owner->canView($member)) {
                return $this->owner->ShareTokenLink();
            }
        }

        return Security::permissionFailure();
    }

    /**
     * @return string
     */
    public function getShareDraftLinkAction()
    {
        if ($this->owner->config()->get('url_segment')) {
            return $this->owner->Link('MakeShareDraftLink');
        }
        return '';
    }
}
