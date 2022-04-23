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

use Craft;
use craft\db\Migration;
use doublesecretagency\starratings\StarRatings;

/**
 * Migration: Convert half-star setting to a dropdown menu
 * @since 2.1.0
 */
class m180701_000000_starRatings_convertHalfStarSetting extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Get settings
        $settings = StarRatings::$plugin->getSettings();

        // If no settings exist, bail
        if (!$settings) {
            return true;
        }

        // Convert model into array
        $settings = $settings->getAttributes();

        // If `allowHalfStars` is not set, bail
        if (!isset($settings['allowHalfStars'])) {
            return true;
        }

        // Set `starIncrements` value
        if (1 == $settings['allowHalfStars']) {
            $settings['starIncrements'] = 'half';
        } else {
            $settings['starIncrements'] = 'full';
        }

        // Remove old setting
        unset($settings['allowHalfStars']);

        // Save settings
        Craft::$app->getPlugins()->savePluginSettings(StarRatings::$plugin, $settings);

        // Return true
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180701_000000_starRatings_convertHalfStarSetting cannot be reverted.\n";

        return false;
    }

}
