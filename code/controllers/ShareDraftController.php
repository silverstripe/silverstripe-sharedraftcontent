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

		$latest = $page->Versions(null, 'Version DESC')->first();

		$controller = new ContentController($latest);

		if(!$shareToken->isExpired() && $page->generateKey($shareToken->Token) === $key) {
			$rendered = $controller->render();

			$data = new ArrayData(array(
				'Page' => $page,
				'Latest' => $latest,
			));

			$include = (string) $data->renderWith('Includes/TopBar');

			return str_replace('</body>', $include . '</body>', (string) $rendered);
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
