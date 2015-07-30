<?php

class ShareDraftContentControllerExtension extends Extension {
	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'MakeShareDraftLink',
	);

	/**
	 * @return mixed
	 */
	public function MakeShareDraftLink() {
		if ($member = Member::currentUser()) {
			if($this->owner->hasMethod('CurrentPage') && $this->owner->CurrentPage()->canEdit($member)) {
				return $this->owner->CurrentPage()->ShareTokenLink();
			} elseif ($this->owner->hasMethod('canEdit') && $this->owner->canEdit($member)) {
				return $this->owner->ShareTokenLink();
			}
		}

		return Security::permissionFailure();
	}

	/**
	 * @return string
	 */
	public function getShareDraftLinkAction() {
		return $this->owner->Link('MakeShareDraftLink');
	}
}
