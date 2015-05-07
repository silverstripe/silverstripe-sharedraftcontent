<?php

class ShareDraftContentRequirementsExtension extends DataExtension {
	public function init() {
		Requirements::css('sharedraftcontent/css/main.css');
		Requirements::javascript('sharedraftcontent/javascript/main.js');
	}
}
