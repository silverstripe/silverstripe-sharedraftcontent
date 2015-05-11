<?php

/**
 * @mixin PHPUnit_Framework_TestCase
 *
 * @package shareddraftcontent
 */
class ShareTokenTest extends FunctionalTest {
	/**
	 * @var string
	 */
	public static $fixture_file = 'shareddraftcontent/tests/ShareTokenTest.yml';

	public function testValidForDays() {
		SS_Datetime::set_mock_now('2015-03-15 00:00:00');

		/**
		 * @var ShareToken $validToken
		 */
		$validToken = $this->objFromFixture('ShareToken', 'ValidToken');

		$this->assertFalse($validToken->isExpired());

		/**
		 * @var ShareToken $invalidToken
		 */
		$invalidToken = $this->objFromFixture('ShareToken', 'InvalidToken');

		$this->assertTrue($invalidToken->isExpired());
	}
}
