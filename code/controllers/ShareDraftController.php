<?php

class ShareDraftController extends Controller {
	/**
	 * Controller for rendering draft pages.
	 *
	 * @config
	 *
	 * @var string
	 */
	private static $controller = 'Page_Controller';

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

		$controller = $this->getControllerFor($latest);

		if(!$shareToken->isExpired() && $page->generateKey($shareToken->Token) === $key) {
			Requirements::css(SHAREDRAFTCONTENT_DIR . '/css/top-bar.css');

			$rendered = $controller->handleRequest($request);

			$data = new ArrayData(array(
				'Page' => $page,
				'Latest' => $latest,
			));

			$include = (string) $data->renderWith('Includes/TopBar');

			return str_replace('</body>', $include . '</body>', (string) $rendered->getBody());
		} else {
			return $this->errorPage();
		}
	}

	/**
	 * @return HTMLText
	 */
	protected function errorPage() {
		Requirements::css(SHAREDRAFTCONTENT_DIR . '/css/error-page.css');

		return $this->renderWith('ShareDraftContentError');
	}

	/**
	 * @param mixed $page
	 *
	 * @return mixed
	 */
	protected function getControllerFor($page) {
		$config = Config::inst()->forClass('ShareDraftController');

		$controller = $config->controller;

		if (!$controller || !class_exists($controller)) {
			return new ContentController($page);
		}

		return new $controller($page);
	}
}
