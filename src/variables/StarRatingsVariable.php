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

namespace doublesecretagency\starratings\variables;

use Craft;
use craft\elements\db\ElementQuery;
use craft\errors\DeprecationException;
use craft\helpers\Template;
use doublesecretagency\starratings\models\Settings;
use doublesecretagency\starratings\StarRatings;
use doublesecretagency\starratings\web\assets\CssAssets;
use doublesecretagency\starratings\web\assets\FieldInputAssets;
use doublesecretagency\starratings\web\assets\FontAwesomeAssets;
use doublesecretagency\starratings\web\assets\JsAssets;
use Twig\Markup;
use yii\base\InvalidConfigException;

/**
 * Class StarRatingsVariable
 * @since 2.0.0
 */
class StarRatingsVariable
{

    /**
     * @var array Disabled resources (CSS and/or JS).
     */
    private array $_disabled = [];

    /**
     * @var bool Whether CSS resources have been included.
     */
    private bool $_cssIncluded = false;

    /**
     * @var bool Whether JavaScript resources have been included.
     */
    private bool $_jsIncluded = false;

    /**
     * @var bool Whether CSRF token has been included.
     */
    private bool $_csrfIncluded = false;

    // ========================================================================= //

    /**
     * Render stars.
     *
     * @param int $elementId
     * @param string|bool|null $key
     * @param bool $allowElementRating
     * @param int|null $userId
     * @return Markup|string
     */
    public function stars(int $elementId, string|bool|null $key = null, bool $allowElementRating = true, ?int $userId = null): Markup|string
    {
        // If element ID is invalid, return error
        if (!$elementId || !is_numeric($elementId)) {
            return 'Invalid element ID';
        }

        // Allow $key parameter to be skipped
        if ((null !== $key) && is_bool($key)) {
            $allowElementRating = $key;
            $key = null;
        }

        // Get ratings
        $avgRating  = StarRatings::$plugin->starRatings_query->avgRating($elementId, $key);
        $userRating = StarRatings::$plugin->starRatings_query->userRating($userId, $elementId, $key);

        // Draw stars
        return $this->_drawStars($avgRating, $userRating, $elementId, $key, $allowElementRating);
    }

    /**
     * Render locked stars.
     *
     * @param float|null $rating
     * @return Markup
     */
    public function lockedStars(?float $rating): Markup
    {
        return $this->_drawStars($rating);
    }

    /**
     * Render form field stars.
     *
     * @param string $fieldHandle
     * @param int|null $existingValue
     * @param string $namespace
     * @return Markup
     * @throws InvalidConfigException
     */
    public function formField(string $fieldHandle, ?int $existingValue = 0, string $namespace = 'fields'): Markup
    {
        // Get view
        $view = Craft::$app->getView();

        // Register assets
        $view->registerAssetBundle(FieldInputAssets::class);

        // Draw stars
        $stars = $this->_drawStars(0, $existingValue);

        // Set HTML
        $input = '<input type="hidden" class="sr-star-input" id="'.$fieldHandle.'" name="'.$fieldHandle.'" value="'.$existingValue.'">';
        $div = '<div class="sr-stars-container">'.$stars.$input.'</div>';

        // If front-end request, apply namespace
        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            $div = $view->namespaceInputs($div, $namespace);
        }

        // Return HTML
        return Template::raw($div);
    }

    /**
     * Draw stars.
     *
     * @param float|null $avgRating
     * @param int|null $userRating
     * @param int|null $elementId
     * @param string|null $key
     * @param bool $allowElementRating
     * @return Markup
     */
    private function _drawStars(?float $avgRating, ?int $userRating = 0, ?int $elementId = null, ?string $key = null, bool $allowElementRating = false): Markup
    {
        /** @var Settings $settings */
        $settings = StarRatings::$plugin->getSettings();

        // Set total number of available stars
        $maxStarsAvailable = $settings->maxStarsAvailable;

        // Get star icons
        $starIcons = StarRatings::$plugin->starRatings_rate->starIcons;

        // If no average rating, set to zero
        if (!$avgRating) {
            $avgRating = 0.0;
        }

        $html = '';
        $partialStar = false;
        $mod = fmod($avgRating, 1);

        // Include CSS
        $this->_includeCss();

        // Loop through all stars
        for ($i = 1; $i <= $maxStarsAvailable; $i++) {

            // Initialize star classes
            $classes = 'sr-star sr-value-'.$i;

            // If back-end field, use larger stars
            if (StarRatings::$plugin->starRatings->backendField) {
                $classes .= ' fa-2x';
            }

            // Whether star is displaying the user's rating
            $userStar = ($userRating && ($i <= $userRating));

            // Whether star is displaying the average rating
            $avgStar = (!$userRating && ($i <= $avgRating));

            // Configure star
            if ($userStar) {

                // User rating
                $starType = 'sr-user-rating';
                $star = $starIcons['4/4'];

            } else if ($avgStar) {

                // Average rating
                $starType = 'sr-avg-rating';
                $star = $starIcons['4/4'];

                // Whether partial stars are allowed
                $allowsPartialStar = (in_array($settings->starIncrements, ['half','quarter']));

                // Determine whether a half star is next
                if ($allowsPartialStar && (0 < $mod)) {

                    // Calculate star size
                    if (0.75 <= $mod) {
                        $iconSize = '3/4';
                    } else if (0.5 <= $mod) {
                        $iconSize = '2/4';
                    } else if (0.25 <= $mod) {
                        $iconSize = '1/4';
                    } else {
                        $iconSize = false;
                    }

                    // Adjust size for half stars
                    if ('half' === $settings->starIncrements) {
                        switch ($iconSize) {
                            case '1/4':
                                $iconSize = false;
                                break;
                            case '3/4':
                                $iconSize = '2/4';
                                break;
                        }
                    }

                    // Set partial star
                    $partialStar = ($iconSize ? $starIcons[$iconSize] : false);
                }

            } else {

                // Remaining stars
                if ($partialStar) {
                    // Partial star
                    $starType = 'sr-avg-rating';
                    $star = $partialStar;
                    $partialStar = false;
                } else {
                    // Empty star
                    $starType = 'sr-unrated';
                    $star = $starIcons['0/4'];
                }

            }

            // Append star type class
            $classes .= ' '.$starType;

            // If element specified
            if ($elementId) {
                $classes .= ' sr-element-'.$elementId.($key ? '-'.$key : '');
            } else {
                $classes .= ' sr-locked';
            }

            // If element is ratable
            $loggedInOrAnonOk = (Craft::$app->user->getIdentity() || !$settings->requireLogin);
            $unratedOrReratable = (!$userRating || $settings->allowRatingChange);
            $ratable = ($elementId && $allowElementRating && $loggedInOrAnonOk && $unratedOrReratable);
            if ($ratable) {
                $classes .= ' sr-ratable';
            }

            // Append star HTML
            $data = 'data-rating="'.$i.'"';
            $onclick = 'onclick="'.$this->_starJs($elementId, $key, $i, $allowElementRating).'"';
            $html .= '<span class="'.$classes.'" '.$data.' '.($elementId ? $onclick : '').'>'.$star.'</span>';
        }

        // Return compiled stars
        return Template::raw($html);
    }

    /**
     * Include CSS.
     *
     * @throws InvalidConfigException
     */
    private function _includeCss(): void
    {
        // If CSS has been included, bail
        if ($this->_cssIncluded) {
            return;
        }

        // If CSS is disabled, bail
        if (in_array('css', $this->_disabled)) {
            return;
        }

        /** @var Settings $settings */
        $settings = StarRatings::$plugin->getSettings();

        // Get view
        $view = Craft::$app->getView();

        // Include CSS resources
        if ($settings->allowFontAwesome) {
            $view->registerAssetBundle(FontAwesomeAssets::class);
        }
        $view->registerAssetBundle(CssAssets::class);

        // Mark CSS as included
        $this->_cssIncluded = true;
    }

    /**
     * Include JS.
     *
     * @throws InvalidConfigException
     */
    private function _includeJs(): void
    {
        /** @var Settings $settings */
        $settings = StarRatings::$plugin->getSettings();

        // If JS has been included, bail
        if ($this->_jsIncluded) {
            return;
        }

        // If JS is disabled, bail
        if (in_array('js', $this->_disabled)) {
            return;
        }

        // Get view
        $view = Craft::$app->getView();

        // Include JS resources
        $view->registerAssetBundle(JsAssets::class);

        // Set icons
        $iconJs = '';
        $starIcons = StarRatings::$plugin->starRatings_rate->starIcons;
        foreach ($starIcons as $key => $icon) {
            $iconJs .= 'starRatings.starIcons["'.$key.'"] = '.json_encode($icon).';'.PHP_EOL;
        }
        $view->registerJs($iconJs, $view::POS_END);

        // Allow Rating Change
        if ($settings->allowRatingChange) {
            $view->registerJs('starRatings.ratingChangeAllowed = true;', $view::POS_END);
        }

        // Get config settings
        $config = Craft::$app->getConfig()->getGeneral();

        // Dev Mode
        if ($config->devMode) {
            $view->registerJs('starRatings.devMode = true;', $view::POS_END);
        }

        // CSRF
        if ($config->enableCsrfProtection) {
            if (!$this->_csrfIncluded) {
                $csrf = '
window.csrfTokenName = "'.$config->csrfTokenName.'";
window.csrfTokenValue = "'.Craft::$app->request->getCsrfToken().'";
';
                $view->registerJs($csrf, $view::POS_END);
                $this->_csrfIncluded = true;
            }
        }

        // Mark JS as included
        $this->_jsIncluded = true;
    }

    /**
     * JavaScript triggers for individual stars.
     *
     * @param int|null $elementId
     * @param string|null $key
     * @param int $value
     * @param bool $allowElementRating
     * @param bool $prefix
     * @return string
     * @throws InvalidConfigException
     */
    private function _starJs(?int $elementId, ?string $key, int $value, bool $allowElementRating, bool $prefix = false): string
    {
        $this->_includeJs();
        $jsKey = ($key ? "'$key'" : 'null');
        $allow = ($allowElementRating ? 'true' : 'false');
        return ($prefix?'javascript:':'')."starRatings.rate($elementId, $jsKey, $value, $allow)";
    }

    // ========================================================================= //

    /**
     * Output average rating of stars.
     *
     * @param int $elementId
     * @param string|null $key
     * @return float
     */
    public function avgRating(int $elementId, ?string $key = null): float
    {
        // If element ID is invalid, log error
        if (!$elementId || !is_numeric($elementId)) {
//            StarRatingsPlugin::log('Invalid element ID');
            return 0;
        }

        return StarRatings::$plugin->starRatings_query->avgRating($elementId, $key);
    }

    /**
     * Output total votes of element.
     *
     * @param int $elementId
     * @param string|null $key
     * @return int
     */
    public function totalVotes(int $elementId, ?string $key = null): int
    {
        // If element ID is invalid, log error
        if (!$elementId || !is_numeric($elementId)) {
//            StarRatingsPlugin::log('Invalid element ID');
            return 0;
        }

        return StarRatings::$plugin->starRatings_query->totalVotes($elementId, $key);
    }

    /**
     * Get rating of specific user.
     *
     * @param int $userId
     * @param int $elementId
     * @param string|null $key
     * @return int
     */
    public function userRating(int $userId, int $elementId, ?string $key = null): int
    {
        // If element ID is invalid, log error
        if (!$elementId || !is_numeric($elementId)) {
//            StarRatingsPlugin::log('Invalid element ID');
            return 0;
        }

        // If user ID is invalid, log error
        if (!$userId || !is_numeric($userId)) {
//            StarRatingsPlugin::log('Invalid user ID');
            return 0;
        }

        return StarRatings::$plugin->starRatings_query->userRating($userId, $elementId, $key);
    }

    // ========================================================================= //

    /**
     * Customize icons.
     *
     * @param array $iconMap
     */
    public function setIcons(array $iconMap = []): void
    {
        StarRatings::$plugin->starRatings_rate->setIcons($iconMap);
    }

    /**
     * Customize icons.
     *
     * @param array $iconMap
     * @throws DeprecationException
     * @deprecated in 2.0.0. Use `setIcons` instead.
     */
    public function setStarIcons(array $iconMap = []): void
    {
        Craft::$app->getDeprecator()->log('craft.starRatings.setStarIcons', 'craft.starRatings.setStarIcons() has been deprecated. Use craft.starRatings.setIcons() instead.');
        $this->setIcons($iconMap);
    }

    /**
     * Sort by "highest rated".
     *
     * @param ElementQuery $elements
     * @param string|null $key
     * @return ElementQuery
     */
    public function sort(ElementQuery $elements, ?string $key = null): ElementQuery
    {
        return StarRatings::$plugin->starRatings_query->orderByAvgRating($elements, $key);
    }

    /**
     * Disable native CSS and/or JavaScript.
     *
     * @param string|array $resources
     * @return array|null
     */
    public function disable(string|array $resources = []): ?array
    {
        if (is_string($resources)) {
            $resources = [$resources];
        }
        if (is_array($resources)) {
            return $this->_disabled = array_map('strtolower', $resources);
        }
        return null;
    }

}
