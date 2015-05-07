<?php

class ShareToken extends DataObject {
	/**
	 * @var array
	 */
	private static $db = array(
		'Token' => 'Varchar(16)',
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'Page' => 'Page'
	);
}
