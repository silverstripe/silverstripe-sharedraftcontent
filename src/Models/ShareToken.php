<?php

namespace SilverStripe\ShareDraftContent\Models;

use Page;
use SilverStripe\Assets\File;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * @property int $ValidForDays
 * @property int $PageID
 * @property string $Token
 * @method Page Page()
 */
class ShareToken extends DataObject
{
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
        'Page' => Page::class,
        'File' => File::class,
    );

    /**
     * @var string
     */
    private static $table_name = 'ShareToken';

    /**
     * Determines whether the token is still valid (from days since it was created).
     *
     * @return bool
     */
    public function isExpired()
    {
        $createdSeconds = strtotime($this->Created ?? '');

        $validForSeconds = (int) $this->ValidForDays * 24 * 60 * 60;

        $nowSeconds = DBDatetime::now()->getTimestamp();

        return ($createdSeconds + $validForSeconds) <= $nowSeconds;
    }
}
