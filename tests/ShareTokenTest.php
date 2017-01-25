<?php

namespace SilverStripe\ShareDraftContent\Tests;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ShareDraftContent\Models\ShareToken;

/**
 * @mixin PHPUnit_Framework_TestCase
 *
 * @package shareddraftcontent
 */
class ShareTokenTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'ShareTokenTest.yml';

    public function testValidForDays()
    {
        DBDatetime::set_mock_now('2015-03-15 00:00:00');

        /**
         * @var ShareToken $validToken
         */
        $validToken = $this->objFromFixture(ShareToken::class, 'ValidToken');

        $this->assertFalse($validToken->isExpired());

        /**
         * @var ShareToken $invalidToken
         */
        $invalidToken = $this->objFromFixture(ShareToken::class, 'InvalidToken');

        $this->assertTrue($invalidToken->isExpired());
    }
}
