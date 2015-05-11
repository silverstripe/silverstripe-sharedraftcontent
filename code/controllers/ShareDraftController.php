<?php

class ShareDraftController extends Controller {
	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'preview'
	);

	/**
	 * @var array
	 */
	private static $url_handlers = array(
		'$Key/$Token' => 'preview'
	);

	/**
	 * @param SS_HTTPRequest $request
	 *
	 * @return string|HTMLText
	 */
	public function preview(SS_HTTPRequest $request) {
		$key = $request->param('Key');
		$token = $request->param('Token');

		/**
		 * @var ShareToken $shareToken
		 */
		$shareToken = ShareToken::get()->filter('token', $token)->first();

		if(!$shareToken) {
			return $this->errorPage();
		}

		$page = $shareToken->Page();

		if(!$shareToken->isExpired() && $page->generateKey($shareToken->Token) === $key) {
			// TODO: Show the draft content
			return $this->render();
		} else {
			return $this->errorPage();
		}
	}

	/**
	 * @return HTMLText
	 */
	protected function errorPage() {
		Requirements::css(SHAREDDRAFTCONTENT_DIR . '/css/main.css');

		return $this->renderWith('ShareDraftContentError');
	}
}
