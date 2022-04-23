<?php
/**
 * Star Ratings plugin for Craft CMS
 *
 * An easy to use and highly flexible ratings system.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2015 Double Secret Agency
 */

namespace doublesecretagency\starratings\records;

use craft\db\ActiveRecord;

/**
 * Class RatingLog
 * @since 2.0.0
 */
class RatingLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%starratings_ratinglog}}';
    }

}
