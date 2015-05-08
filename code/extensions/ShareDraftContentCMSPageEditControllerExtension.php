<?php

class ShareDraftContentCMSPageEditControllerExtension extends DataExtension {

	private static $allowed_actions = array(
		'makelink'
	);

	public function getMakeLinkAction() {
		return $this->owner->Link('makelink');
	}

	public function makelink() {
		return $this->owner->currentPage()->ShareTokenLink();
	}
}
