<?php

class ShareDraftContentExtension extends DataExtension {
	/**
	 * @var array
	 */
	private static $db = array(
		'ShareTokenSalt' => 'Varchar(16)',
	);

	/**
	 * @var array
	 */
	private static $has_many = array(
		'ShareTokens' => 'ShareToken',
	);

	/**
	 * @return string
	 */
	public function ShareTokenLink() {
		$shareToken = $this->getNewShareToken();

		return Controller::join_links($this->owner->Link(), 'preview', $this->generateKey($shareToken->Token), $shareToken->Token);
	}

	/**
	 * @return ShareToken
	 */
	protected function getNewShareToken() {
		if(!$this->owner->ShareTokenSalt) {
			$this->owner->ShareTokenSalt = $this->getNewToken();
			$this->owner->write();
		}

		$found = null;
		$token = null;
		$tries = 1;
		$limit = 5;

		while(!$found && ($tries++ < $limit)) {
			$token = $this->getNewToken();

			$found = ShareToken::get()->filter(array(
				"Token" => $token,
				"PageID" => $this->owner->ID,
			))->first();
		}

		$token = ShareToken::create(array(
			"Token" => $token,
			"PageID" => $this->owner->ID,
		));

		$token->write();

		return $token;
	}

	/**
	 * @return string
	 */
	protected function getNewToken() {
		$generator = new RandomGenerator();

		return substr($generator->randomToken('sha256'), 0, 16);
	}

	/**
	 * @param string $salt
	 *
	 * @return string
	 */
	public function generateKey($salt) {
		return hash_pbkdf2('sha256', $salt, $this->owner->SharedTokenSalt, 1000, 16);
	}
}
