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
use craft\db\Query;

/**
 * Migration: Convert half-star setting to a dropdown menu
 * @since 2.1.0
 */
class m180701_000000_starRatings_convertHalfStarSetting extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Get settings
        $settings = $this->_getSettings();

        // If no settings exist, bail
        if (!is_array($settings)) {
            return true;
        }

        // If `allowHalfStars` is not set, bail
        if (!array_key_exists('allowHalfStars', $settings)) {
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
        $this->_setSettings($settings);

        // Return true
        return true;
    }

    /**
     * Get plugin settings
     *
     * @return array
     */
    private function _getSettings()
    {
        // Get original settings value
        $oldSettings = (new Query())
            ->select(['settings'])
            ->from(['{{%plugins}}'])
            ->where(['handle' => 'star-ratings'])
            ->one($this->db);
        return @json_decode($oldSettings['settings'], true);
    }

    /**
     * Save plugin settings
     *
     * @param array $settings Updated settings
     */
    private function _setSettings($settings)
    {
        // Update settings field
        $newSettings = json_encode($settings);
        $data = ['settings' => $newSettings];
        $this->update('{{%plugins}}', $data, ['handle' => 'star-ratings']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180701_000000_starRatings_convertHalfStarSetting cannot be reverted.\n";

        return false;
    }

}