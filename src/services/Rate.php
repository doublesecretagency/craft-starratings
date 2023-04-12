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

namespace doublesecretagency\starratings\services;

use Craft;
use craft\base\Component;
use craft\elements\User;
use craft\errors\DeprecationException;
use craft\helpers\Json;
use doublesecretagency\starratings\models\Settings;
use doublesecretagency\starratings\StarRatings;
use doublesecretagency\starratings\events\RateEvent;
use doublesecretagency\starratings\records\ElementRating;
use doublesecretagency\starratings\records\RatingLog;
use doublesecretagency\starratings\records\UserHistory;
use yii\base\Event;
use yii\web\Cookie;

/**
 * Class Rate
 * @since 2.0.0
 */
class Rate extends Component
{

    /**
     * @var array Set of icons for displaying ratings.
     */
    public array $starIcons = [];

    /**
     * @var string Potential error messages.
     */
    public string $messageLoginRequired    = 'You must be logged in to rate this element.';
    public string $messageAlreadyRated     = 'You have already rated this element.';
    public string $messageChangeDisallowed = 'Unable to change rating. Rate changing is disabled.';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->_loadIcons();
    }

    /**
     * Default icons for displaying ratings.
     */
    private function _loadIcons(): void
    {
        $this->starIcons = [
            '0/4' => $this->_fa('star-o'),
            '1/4' => $this->_fa('star-o'),
            '2/4' => $this->_fa('star-half-empty'),
            '3/4' => $this->_fa('star-half-empty'),
            '4/4' => $this->_fa('star'),
        ];
    }

    /**
     * Generate an icon using Font Awesome.
     *
     * @param string $iconType
     * @return string
     */
    private function _fa(string $iconType): string
    {
        return '<i class="fa fa-'.$iconType.'"></i>';
    }

    /**
     * Set new icons to display ratings.
     *
     * @param array $iconMap
     */
    public function setIcons(array $iconMap = []): void
    {
        foreach ($iconMap as $type => $html) {
            switch ($type) {
                case 'empty':
                case '0/4':
                    $this->starIcons['0/4'] = $html;
                    break;
                case '1/4':
                    $this->starIcons['1/4'] = $html;
                    break;
                case 'half':
                case '2/4':
                    $this->starIcons['2/4'] = $html;
                    break;
                case '3/4':
                    $this->starIcons['3/4'] = $html;
                    break;
                case 'full':
                case '4/4':
                    $this->starIcons['4/4'] = $html;
                    break;
            }
        }
    }

    /**
     * Set new icons to display ratings.
     *
     * @param array $iconMap
     * @throws DeprecationException
     * @deprecated in 2.0.0. Use `setIcons` instead.
     */
    public function setStarIcons(array $iconMap = []): void
    {
        Craft::$app->getDeprecator()->log('Rate::setStarIcons', 'Rate::setStarIcons() has been deprecated. Use setIcons() instead.');
        $this->setIcons($iconMap);
    }

    // ========================================================================= //

    /**
     * Cast a new rating.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int $rating
     * @param int|User|null $userId
     * @return array|string
     */
    public function rate(int $elementId, ?string $key, int $rating, int|User|null $userId = null): array|string
    {
        /** @var Settings $settings */
        $settings = StarRatings::$plugin->getSettings();

        // Ensure the user ID is valid
        StarRatings::$plugin->starRatings->validateUserId($userId);

        // Get old rating by this user (if exists)
        $oldRating = StarRatings::$plugin->starRatings_query->userRating($userId, $elementId, $key);

        // Does old rating exist, and is it different?
        $changed = ($oldRating && ($oldRating != $rating));

        // Is change allowed?
        $changeAllowed = $settings->allowRatingChange;

        // Ensure change is allowed
        if ($changed && !$changeAllowed) {
            return $this->messageChangeDisallowed;
        }

        // Prep return data
        $returnData = [
            'id'          => $elementId,
            'key'         => $key,
            'rating'      => $rating,
            'changedFrom' => $oldRating,
            'userId'      => $userId,
        ];

        // Trigger event before a rating is cast
        if (Event::hasHandlers(StarRatings::class, StarRatings::EVENT_BEFORE_RATE)) {
            Event::trigger(StarRatings::class, StarRatings::EVENT_BEFORE_RATE, new RateEvent($returnData));
        }

        // Cast element rating, get potential error message.
        $message = $this->_rateElement($elementId, $key, $rating, $userId, $changed, $oldRating);

        // Trigger event after a rating is cast
        if (Event::hasHandlers(StarRatings::class, StarRatings::EVENT_AFTER_RATE)) {
            Event::trigger(StarRatings::class, StarRatings::EVENT_AFTER_RATE, new RateEvent($returnData));
        }

        // Return error message or data
        return ($message ?: $returnData);
    }

    /**
     * Cast a new rating.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int $rating
     * @param int|null $oldRating
     * @param int|null $userId
     * @deprecated in 2.0.0. Use `rate` instead.
     */
    public function changeRating(int $elementId, ?string $key, int $rating, ?int $oldRating = null, ?int $userId = null): void
    {
        $this->rate($elementId, $key, $rating, $userId);
    }

    // ========================================================================= //

    /**
     * Internally cast a new rating.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int $rating
     * @param int|null $userId
     * @param bool $changed
     * @param int|null $oldRating
     * @return string|null
     */
    private function _rateElement(int $elementId, ?string $key, int $rating, ?int $userId, bool $changed, ?int $oldRating): ?string
    {
        /** @var Settings $settings */
        $settings = StarRatings::$plugin->getSettings();

        // If changed, remove existing rating
        if ($changed) {
            $this->_removeRatingFromDb($elementId, $key, $userId);
            $this->_removeRatingFromCookie($elementId, $key);
            $this->_updateElementAvgRating($elementId, $key, $oldRating, true);
        }

        // If login is required
        if ($settings->requireLogin) {
            // Update user history
            if (!$this->_updateUserHistoryDatabase($elementId, $key, $rating, $userId)) {
                return $this->messageAlreadyRated;
            }
        } else {
            // Update user cookie
            if (!$this->_updateUserHistoryCookie($elementId, $key, $rating)) {
                return $this->messageAlreadyRated;
            }
        }

        // Update element average rating
        $this->_updateElementAvgRating($elementId, $key, $rating);
        $this->_updateRatingLog($elementId, $key, $rating, $userId, $changed);

        // No message by default
        return null;
    }

    /**
     * Update the logged-in user history in the database.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int $rating
     * @param int|null $userId
     * @return bool
     */
    private function _updateUserHistoryDatabase(int $elementId, ?string $key, int $rating, ?int $userId): bool
    {
        // If user is not logged in, return false
        if (!$userId) {
            return false;
        }
        // Load existing element history
        $record = UserHistory::findOne([
            'id' => $userId,
        ]);

        // Get item key
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);

        // If record already exists
        if ($record) {
            $history = Json::decode($record->history);
            // If user has already rated element, bail
            if (isset($history[$item])) {
                return false;
            }
        } else {
            // Create new record if necessary
            $record = new UserHistory;
            $record->id = $userId;
            $history = [];
        }

        // Register rating
        $history[$item] = $rating;
        $record->history = $history;

        // Save
        return $record->save();
    }

    /**
     * Update the anonymous user history cookie.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int $rating
     * @return bool
     */
    private function _updateUserHistoryCookie(int $elementId, ?string $key, int $rating): bool
    {
        $history =& StarRatings::$plugin->starRatings->anonymousHistory;
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);
        // If already voted for, bail
        if (isset($history[$item])) {
            return false;
        }
        // Cast vote
        $history[$item] = $rating;
        $this->saveUserHistoryCookie();
        return true;
    }

    /**
     * Save the anonymous user history cookie.
     */
    public function saveUserHistoryCookie(): void
    {
        // If running via command line, bail
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }
        // Get cookie settings
        $cookieName = StarRatings::$plugin->starRatings->userCookie;
        $history    = StarRatings::$plugin->starRatings->anonymousHistory;
        $lifespan   = StarRatings::$plugin->starRatings->userCookieLifespan;
        // Set cookie
        $cookie = new Cookie();
        $cookie->name = $cookieName;
        $cookie->value = json_encode($history);
        $cookie->expire = time() + $lifespan;
        Craft::$app->getResponse()->getCookies()->add($cookie);
    }

    /**
     * Update the element's average rating.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int $rating
     * @param bool $removingRating
     * @return bool
     */
    private function _updateElementAvgRating(int $elementId, ?string $key, int $rating, bool $removingRating = false): bool
    {
        // Load existing element avgRating
        $record = ElementRating::findOne([
            'elementId' => $elementId,
            'starKey'   => $key,
        ]);
        // If average rating exists
        if ($record) {
            // Get current grand total
            $grandTotal = ($record->avgRating * $record->totalVotes);
            // If undoing a rating
            if ($removingRating) {
                $grandTotal -= $rating;
                $record->totalVotes--;
            } else {
                $grandTotal += $rating;
                $record->totalVotes++;
            }
            // If votes exist, calculate average rating
            if ($record->totalVotes && ($record->totalVotes > 0)) {
                $record->avgRating = ($grandTotal / $record->totalVotes);
            } else {
                $record->avgRating = 0;
            }
        } else if (!$removingRating) {
            // Create new
            $record = new ElementRating;
            $record->elementId  = $elementId;
            $record->starKey    = $key;
            $record->avgRating  = $rating;
            $record->totalVotes = 1;
        }
        // If no record, bail
        if (!$record) {
            return false;
        }
        // Save
        return $record->save();
    }

    /**
     * Update the element rating log.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int $rating
     * @param int|null $userId
     * @param bool $changed
     */
    private function _updateRatingLog(int $elementId, ?string $key, int $rating, ?int $userId, bool $changed): void
    {
        // If not keeping a rating log, bail
        if (!StarRatings::$plugin->getSettings()->keepRatingLog) {
            return;
        }

        // Log rating
        $record = new RatingLog;
        $record->elementId     = $elementId;
        $record->starKey       = $key;
        $record->userId        = $userId;
        $record->ipAddress     = $_SERVER['REMOTE_ADDR'];
        $record->ratingValue   = $rating;
        $record->ratingChanged = (int) $changed;
        $record->save();
    }

    /**
     * Remove a rating from the database.
     *
     * @param int $elementId
     * @param string|null $key
     * @param int|null $userId
     */
    private function _removeRatingFromDb(int $elementId, ?string $key, ?int $userId): void
    {
        // If no user ID, bail
        if (!$userId) {
            return;
        }

        // Get user history
        $record = UserHistory::findOne([
            'id' => $userId,
        ]);

        // If no user history, bail
        if (!$record) {
            return;
        }

        // Remove from database history
        $historyDb = Json::decode($record->history);
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);

        // If item doesn't exist in history, bail
        if (!isset($historyDb[$item])) {
            return;
        }

        // Remove item from history
        unset($historyDb[$item]);
        $record->history = $historyDb;
        $record->save();
    }

    /**
     * Remove a rating from the anonymous cookie.
     *
     * @param int $elementId
     * @param string|null $key
     */
    private function _removeRatingFromCookie(int $elementId, ?string $key): void
    {
        // Get user history
        $history =& StarRatings::$plugin->starRatings->anonymousHistory;

        // If no user history, bail
        if (!$history) {
            return;
        }

        // Get the item key
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);

        // If item doesn't exist in history, bail
        if (!isset($history[$item])) {
            return;
        }

        // Remove item from history
        unset($history[$item]);

        // Save the updated cookie
        $this->saveUserHistoryCookie();
    }

}
