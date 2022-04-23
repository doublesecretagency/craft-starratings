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

namespace doublesecretagency\starratings\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use doublesecretagency\starratings\StarRatings;

/**
 * Class AvgUserRating
 * @since 2.0.0
 *
 * @property-read null|string $settingsHtml
 */
class AvgUserRating extends Field implements PreviewableFieldInterface
{

    /**
     * @var string|null Unique key for multiple ratings.
     */
    public ?string $starKey = null;

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $AverageUserRating = Craft::t('star-ratings', 'Average User Rating');
        $StarRatings       = Craft::t('star-ratings', 'Star Ratings');
        return "{$AverageUserRating} ({$StarRatings})";
    }

    /**
     * @inheritdoc
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    // ========================================================================= //

    /**
     * Prep value for use as the data leaves the database.
     *
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): float
    {
        return $this->_getAverage($element);
    }

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        // Render field type settings template
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/avguserrating-settings', [
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        // Use large stars
        StarRatings::$plugin->starRatings->backendField = true;
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/avguserrating', [
            'avgRating' => $this->_getAverage($element)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/avguserrating-column', [
            'avgRating' => $this->_getAverage($element)
        ]);
    }

    // ========================================================================= //

    /**
     * Calculate average rating of element
     *
     * @param $element
     * @return float
     */
    private function _getAverage($element): float
    {
        return StarRatings::$plugin->starRatings_query->avgRating($element->id, $this->starKey);
    }

}
