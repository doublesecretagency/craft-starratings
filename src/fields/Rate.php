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
use craft\helpers\Db;

use doublesecretagency\starratings\StarRatings;

/**
 * Class Rate
 * @since 2.0.0
 */
class Rate extends Field implements PreviewableFieldInterface
{

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $Rate        = Craft::t('star-ratings', 'Rate');
        $StarRatings = Craft::t('star-ratings', 'Star Ratings');
        return "{$Rate} ({$StarRatings})";
    }

    /**
     * @inheritdoc
     */
    public static function hasContentColumn(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Db::getNumericalColumnType(0, 10);
    }

    // ========================================================================= //

    /**
     * After saving element, save field to plugin table.
     *
     * @inheritdoc
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        // Get field value
        $value = $element->getFieldValue($this->handle);
        // If not numeric, set as null
        if (!is_numeric($value)) {
            $value = null;
        }
        return $value;
    }

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): string
    {
        // Render fieldtype settings template
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/rate-settings');
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        StarRatings::$plugin->starRatings->backendField = true;
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/rate', [
            'name' => $this->handle,
            'value' => $value,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/rate-column', [
            'value' => $value
        ]);
    }

}