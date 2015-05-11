<?php

/**
 * @method Page Page()
 *
 * @property int $ValidForDays
 * @property string $Token
 */
class ShareToken extends DataObject {
	/**
	 * @var array
	 */
	private static $db = array(
		'Token' => 'Varchar(16)',
		'ValidForDays' => 'Int',
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'Page' => 'Page'
	);

	/**
	 * Determines whether the token is still valid (from days since it was created).
	 *
	 * @return bool
	 */
	public function isExpired() {
		$createdSeconds = strtotime($this->Created);

		$validForSeconds = (int) $this->ValidForDays * 24 * 60 * 60;

		$nowSeconds = strtotime(SS_DateTime::now()->Format("Y-m-d H:i:s"));

		return ($createdSeconds + $validForSeconds) <= $nowSeconds;
	}
}
