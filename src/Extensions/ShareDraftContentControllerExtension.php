<?php

namespace SilverStripe\ShareDraftContent\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * @extends Extension<Controller>
 */
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
            if ($this->owner->hasMethod('currentRecord')) {
                $link = $this->getShareTokenLink($this->owner->currentRecord(), $member);
            } elseif ($this->owner->hasMethod('CurrentPage')) {
                // Could be a non-LeftAndMain controller, since the extension is applied directly to Controller
                $link = $this->getShareTokenLink($this->owner->CurrentPage(), $member);
            }
            $link ??= $this->getShareTokenLink($this->owner, $member);
        }
        if ($link) {
            return $link;
        }

        return Security::permissionFailure();
    }

    private function getShareTokenLink(object $record, Member $member): ?string
    {
        if ($record->hasMethod('canView') && $record->canView($member)) {
            return $record->ShareTokenLink();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getShareDraftLinkAction()
    {
        $owner = $this->getOwner();
        if (!$owner->config()->get('url_segment')) {
            return '';
        }
        $id = $this->getRecordID();
        if (!$id) {
            return '';
        }
        return $owner->Link(Controller::join_links('MakeShareDraftLink', $id));
    }

    private function getRecordID(): ?int
    {
        $owner = $this->getOwner();
        if ($owner->hasMethod('currentRecordID')) {
            return $owner->currentRecordID();
        }
        // Could be a non-LeftAndMain controller, since the extension is applied directly to Controller
        if ($owner->hasMethod('CurrentPage')) {
            return $owner->CurrentPage()?->ID;
        }
        return null;
    }
}
