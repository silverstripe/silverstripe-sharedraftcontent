<?php

class ShareDraftContentRequirementsExtension extends DataExtension {
	public function init() {
		Requirements::css(SHAREDDRAFTCONTENT_DIR . '/css/main.css');
		Requirements::javascript(SHAREDDRAFTCONTENT_DIR . '/javascript/main.js');
	}
}
