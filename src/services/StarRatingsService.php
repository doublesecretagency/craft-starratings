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

use doublesecretagency\starratings\StarRatings;

/**
 * Class StarRatingsService
 * @since 2.0.0
 */
class StarRatingsService extends Component
{

    public $userCookie = 'RatingHistory';
    public $userCookieLifespan = 315569260; // Lasts 10 years
    public $anonymousHistory;

    public $backendField = false;

    // Generate combined item key
    public function setItemKey($elementId, $key)
    {
        return $elementId.($key ? ':'.$key : '');
    }

    // Get history of anonymous user
    public function getAnonymousHistory()
    {
        // Get request
        $request = Craft::$app->getRequest();

        // If running via command line, bail
        if ($request->getIsConsoleRequest()) {
            return false;
        }

        // If login is required, bail
        if (StarRatings::$plugin->getSettings()->requireLogin) {
            return false;
        }

        // Get cookies object
        $cookies = $request->getCookies();

        // If cookie exists
        if ($cookies->has($this->userCookie)) {
            // Get anonymous history
            $cookieValue = $cookies->getValue($this->userCookie);
            $this->anonymousHistory = json_decode($cookieValue, true);
        }

        // If no anonymous history
        if (!$this->anonymousHistory) {
            // Initialize anonymous history
            $this->anonymousHistory = [];
            StarRatings::$plugin->starRatings_rate->saveUserHistoryCookie();
        }

    }

    // $userId can be valid user ID or UserModel
    public function validateUserId(&$userId)
    {
        // No user by default
        $user = null;

        // Handle user ID
        if (!$userId) {
            // Default to logged in user
            $user = Craft::$app->user->getIdentity();
        } else {
            if (is_numeric($userId)) {
                // Get valid UserModel
                $user = Craft::$app->users->getUserById($userId);
            } else if (is_object($userId) && is_a($userId, 'craft\\elements\\User')) {
                // It's already a User model
                $user = $userId;
            }
        }

        // Get user ID, or rate anonymously
        $userId = ($user ? $user->id : null);
    }

}