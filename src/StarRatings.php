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

namespace doublesecretagency\starratings;

use yii\base\Event;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;

use doublesecretagency\starratings\models\Settings;
use doublesecretagency\starratings\services\StarRatingsService;
use doublesecretagency\starratings\services\Query;
use doublesecretagency\starratings\services\Rate;
use doublesecretagency\starratings\variables\StarRatingsVariable;
use doublesecretagency\starratings\web\assets\SettingsAssets;

use doublesecretagency\starratings\fields\AvgUserRating;
use doublesecretagency\starratings\fields\Rate as RateField;

/**
 * Class StarRatings
 * @since 2.0.0
 */
class StarRatings extends Plugin
{

    /** @event RateEvent The event that is triggered before a rating is cast. */
    const EVENT_BEFORE_RATE = 'beforeRate';

    /** @event RateEvent The event that is triggered after a rating is cast. */
    const EVENT_AFTER_RATE = 'afterRate';

    /** @var Plugin  $plugin  Self-referential plugin property. */
    public static $plugin;

    /** @var bool  $hasCpSettings  The plugin has a settings page. */
    public $hasCpSettings = true;

    /** @var bool  $schemaVersion  Current schema version of the plugin. */
    public $schemaVersion = '2.1.0';

    /** @inheritDoc */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Load plugin components
        $this->setComponents([
            'starRatings'       => StarRatingsService::class,
            'starRatings_query' => Query::class,
            'starRatings_rate'  => Rate::class,
        ]);

        // Load anonymous history (if relevant)
        $this->starRatings->getAnonymousHistory();

        // Register field types
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = AvgUserRating::class;
                $event->types[] = RateField::class;
            }
        );

        // Register variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('starRatings', StarRatingsVariable::class);
            }
        );

    }

    /**
     * @return Settings  Plugin settings model.
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @return string  The fully rendered settings template.
     */
    protected function settingsHtml(): string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(SettingsAssets::class);
        $overrideKeys = array_keys(Craft::$app->getConfig()->getConfigFromFile('star-ratings'));
        return $view->renderTemplate('star-ratings/settings', [
            'settings' => $this->getSettings(),
            'overrideKeys' => $overrideKeys,
            'docsUrl' => $this->documentationUrl,
        ]);
    }

}