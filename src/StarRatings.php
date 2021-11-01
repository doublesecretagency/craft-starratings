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

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\db\ElementQuery;
use craft\events\DefineBehaviorsEvent;
use craft\events\DefineGqlTypeFieldsEvent;
use craft\events\PopulateElementEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterGqlMutationsEvent;
use craft\events\RegisterGqlQueriesEvent;
use craft\gql\TypeManager;
use craft\helpers\StringHelper;
use craft\services\Fields;
use craft\services\Gql;
use craft\web\twig\variables\CraftVariable;
use doublesecretagency\starratings\behaviors\AvgRatingBehavior;
use doublesecretagency\starratings\fields\AvgUserRating;
use doublesecretagency\starratings\fields\Rate as RateField;
use doublesecretagency\starratings\gql\mutations\Rate as GqlRate;
use doublesecretagency\starratings\gql\queries\Query as GqlQuery;
use doublesecretagency\starratings\models\Settings;
use doublesecretagency\starratings\records\ElementRating;
use doublesecretagency\starratings\services\Query;
use doublesecretagency\starratings\services\Rate;
use doublesecretagency\starratings\services\StarRatingsService;
use doublesecretagency\starratings\variables\StarRatingsVariable;
use doublesecretagency\starratings\web\assets\SettingsAssets;
use GraphQL\Type\Definition\Type;
use yii\base\Event;

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

        // Register behaviors
        Event::on(
            Element::class,
            Element::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors['avgRating'] = AvgRatingBehavior::class;
            }
        );

        // Get the average ratings during the element query
        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_BEFORE_PREPARE,
            function (Event $event) {
                // Generate a random string for unique table identification
                $rand = StringHelper::randomString(6);
                /** @var ElementQuery $query */
                $query = $event->sender;
                // If getting the COUNT, bail to avoid an error
                if ($query->select === ['COUNT(*)']) {
                    return;
                }
                // If searching, bail to avoid an error
                if ($query->select == ['elements.id' => 'elements.id']) {
                    return;
                }
                // Join the `elementratings` table to get `avgRating`
                $query->addSelect("[[elementratings_{$rand}.avgRating]]");
                $query->leftJoin(
                    ["elementratings_{$rand}" => ElementRating::tableName()],
                    "[[elements.id]] = [[elementratings_{$rand}.elementId]]"
                );
            }
        );

        // Store the average rating of each element
        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_AFTER_POPULATE_ELEMENT,
            function (PopulateElementEvent $event) {
                $entry = $event->element;
                $entry->avgRating = (float) $event->row['avgRating'];
            }
        );

        // Register GraphQL queries
        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_QUERIES,
            function (RegisterGqlQueriesEvent $event) {
                $queries = GqlQuery::getQueries();
                foreach ($queries as $key => $value) {
                    $event->queries[$key] = $value;
                }
            }
        );

        // Register GraphQL mutations
        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_MUTATIONS,
            function (RegisterGqlMutationsEvent $event) {
                $mutations = GqlRate::getMutations();
                foreach ($mutations as $key => $value) {
                    $event->mutations[$key] = $value;
                }
            }
        );

        // Register GraphQL type fields
        Event::on(
            TypeManager::class,
            TypeManager::EVENT_DEFINE_GQL_TYPE_FIELDS,
            function (DefineGqlTypeFieldsEvent $event) {
                $event->fields['avgRating'] = [
                    'name' => 'avgRating',
                    'description' => "The element's average star rating.",
                    'type' => Type::float(),
                    'resolve' => function ($source) {
                        return $source->avgRating;
                    }
                ];
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
