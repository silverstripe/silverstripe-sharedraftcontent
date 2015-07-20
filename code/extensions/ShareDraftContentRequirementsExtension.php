<?php

class ShareDraftContentRequirementsExtension extends DataExtension {
	public function init() {
		Requirements::css(SHAREDRAFTCONTENT_DIR . '/css/share-component.css');
		Requirements::javascript(SHAREDRAFTCONTENT_DIR . '/javascript/main.js');
	}
}
