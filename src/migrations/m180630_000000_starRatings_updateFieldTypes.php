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

namespace doublesecretagency\starratings\migrations;

use craft\db\Migration;
use doublesecretagency\starratings\fields\AvgUserRating;
use doublesecretagency\starratings\fields\Rate;

/**
 * Migration: Update field types for Craft 3 compatibility
 * @since 2.1.0
 */
class m180630_000000_starRatings_updateFieldTypes extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        // Auto-update existing Rate fields
        $this->update('{{%fields}}', [
            'type' => Rate::class
        ], [
            'type' => 'StarRatings_Rate'
        ], [], false);

        // Auto-update existing AvgUserRating fields
        $this->update('{{%fields}}', [
            'type' => AvgUserRating::class
        ], [
            'type' => 'StarRatings_AvgUserRating'
        ], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180630_000000_starRatings_updateFieldTypes cannot be reverted.\n";

        return false;
    }

}
