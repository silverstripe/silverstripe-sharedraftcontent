<?php

class ShareDraftContentRequirementsExtension extends DataExtension {
	public function init() {
		Requirements::css('sharedraftcontent/css/shareDraftContent.css');
		Requirements::javascript('sharedraftcontent/javascript/main.js');
	}
}
