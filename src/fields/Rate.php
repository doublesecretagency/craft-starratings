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
 *
 * @property-read string|array $contentColumnType
 * @property-read null|string $settingsHtml
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
    public function getContentColumnType(): array|string
    {
        return Db::getNumericalColumnType(0, 10);
    }

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        // Render field type settings template
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/rate-settings');
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
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
    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        return Craft::$app->getView()->renderTemplate('star-ratings/fields/rate-column', [
            'value' => $value
        ]);
    }

}
