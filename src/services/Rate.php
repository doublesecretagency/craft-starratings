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

use yii\base\Event;
use yii\web\Cookie;

use Craft;
use craft\base\Component;

use doublesecretagency\starratings\StarRatings;
use doublesecretagency\starratings\events\RateEvent;
use doublesecretagency\starratings\records\ElementRating;
use doublesecretagency\starratings\records\RatingLog;
use doublesecretagency\starratings\records\UserHistory;

/**
 * Class Rate
 * @since 2.0.0
 */
class Rate extends Component
{

    public $starIconFull;
    public $starIconHalf;
    public $starIconEmpty;

    public $messageLoginRequired    = 'You must be logged in to rate this element.';
    public $messageAlreadyRated     = 'You have already rated this element.';
    public $messageChangeDisallowed = 'Unable to change rating. Rate changing is disabled.';

    //
    public function init()
    {
        parent::init();
        $this->_loadIcons();
    }

    //
    private function _loadIcons()
    {
        $this->starIconFull  = $this->_fa('star');
        $this->starIconHalf  = $this->_fa('star-half-empty');
        $this->starIconEmpty = $this->_fa('star-o');
    }

    //
    private function _fa($iconType)
    {
        return '<i class="fa fa-'.$iconType.'"></i>';
    }

    //
    public function setIcons($iconMap = array())
    {
        foreach ($iconMap as $type => $html) {
            switch ($type) {
                case 'full'  : $this->starIconFull  = $html; break;
                case 'half'  : $this->starIconHalf  = $html; break;
                case 'empty' : $this->starIconEmpty = $html; break;
            }
        }
    }

    // DEPRECATED: Use `setIcons` instead
    public function setStarIcons($iconMap = array())
    {
        Craft::$app->getDeprecator()->log('Rate::setStarIcons', 'Rate::setStarIcons() has been deprecated. Use setIcons() instead.');
        return $this->setIcons($iconMap);
    }

    // ========================================================================= //

    //
    public function rate($elementId, $key, $rating, $userId = null)
    {
        // Get settings
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

        // Cast element rating. Get potential error message.
        $message = $this->_rateElement($elementId, $key, $rating, $userId, $changed, $oldRating);

        // Trigger event after a rating is cast
        if (Event::hasHandlers(StarRatings::class, StarRatings::EVENT_AFTER_RATE)) {
            Event::trigger(StarRatings::class, StarRatings::EVENT_AFTER_RATE, new RateEvent($returnData));
        }

        // Return error message or data
        return ($message ? $message : $returnData);
    }

    // DEPRECATED: Use `rate` instead
    public function changeRating($elementId, $key, $rating, $oldRating = null, $userId = null)
    {
        $this->rate($elementId, $key, $rating, $userId);
    }

    // ========================================================================= //

    //
    private function _rateElement($elementId, $key, $rating, $userId, $changed, $oldRating)
    {
        // Get settings
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

    //
    private function _updateUserHistoryDatabase($elementId, $key, $rating, $userId)
    {
        // If user is not logged in, return false
        if (!$userId) {
            return false;
        }
        // Load existing element history
        $record = UserHistory::findOne([
            'id' => $userId,
        ]);
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);
        // If record already exists
        if ($record) {
            $history = json_decode($record->history, true);
            // If user has already rated element, bail
            if (array_key_exists($item, $history)) {
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

    //
    private function _updateUserHistoryCookie($elementId, $key, $rating)
    {
        $history =& StarRatings::$plugin->starRatings->anonymousHistory;
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);
        // If already voted for, bail
        if (array_key_exists($item, $history)) {
            return false;
        }
        // Cast vote
        $history[$item] = $rating;
        $this->saveUserHistoryCookie();
        return true;
    }

    //
    public function saveUserHistoryCookie()
    {
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

    //
    private function _updateElementAvgRating($elementId, $key, $rating, $removingRating = false)
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

    //
    private function _updateRatingLog($elementId, $key, $rating, $userId, $changed)
    {
        // Get settings
        $settings = StarRatings::$plugin->getSettings();
        // If not keeping a rating log, bail
        if (!$settings->keepRatingLog) {
            return false;
        }
        $record = new RatingLog;
        $record->elementId     = $elementId;
        $record->starKey       = $key;
        $record->userId        = $userId;
        $record->ipAddress     = $_SERVER['REMOTE_ADDR'];
        $record->ratingValue   = $rating;
        $record->ratingChanged = (int) $changed;
        $record->save();
    }

    //
    private function _removeRatingFromDb($elementId, $key, $userId)
    {
        // If no user ID, bail
        if (!$userId) {
            return false;
        }
        // Get user history
        $record = UserHistory::findOne([
            'id' => $userId,
        ]);
        // If no user history, bail
        if (!$record) {
            return false;
        }
        // Remove from database history
        $historyDb = json_decode($record->history, true);
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);
        // If item doesn't exist in history, bail
        if (!array_key_exists($item, $historyDb)) {
            return false;
        }
        // Remove item from history
        unset($historyDb[$item]);
        $record->history = $historyDb;
        $record->save();
    }

    //
    private function _removeRatingFromCookie($elementId, $key)
    {
        // Get user history
        $history =& StarRatings::$plugin->starRatings->anonymousHistory;
        // If no user history, bail
        if (!$history) {
            return false;
        }
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);
        // If item doesn't exist in history, bail
        if (!array_key_exists($item, $history)) {
            return false;
        }
        // Remove item from history
        unset($history[$item]);
        $this->saveUserHistoryCookie();
    }

}