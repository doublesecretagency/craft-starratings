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
use doublesecretagency\starratings\StarRatings;

/**
 * Class StarRatingsService
 * @since 2.0.0
 */
class StarRatingsService extends Component
{

    /**
     * @var string Name of cookie containing user rating history.
     */
    public string $userCookie = 'RatingHistory';

    /**
     * @var int Lifespan of cookie containing user rating history.
     */
    public int $userCookieLifespan = 315569260; // Lasts 10 years

    /**
     * @var array Anonymous user history.
     */
    public array $anonymousHistory = [];

    /**
     * @var bool Whether we're loading a backend field.
     */
    public bool $backendField = false;

    // ========================================================================= //

    /**
     * Generate combined item key.
     *
     * @param int $elementId
     * @param string|null $key
     * @return string
     */
    public function setItemKey(int $elementId, ?string $key): string
    {
        return $elementId.($key ? ":{$key}" : '');
    }

    /**
     * Load history of an anonymous user.
     */
    public function getAnonymousHistory(): void
    {
        // Get request
        $request = Craft::$app->getRequest();

        // If running via command line, bail
        if ($request->getIsConsoleRequest()) {
            return;
        }

        // If login is required, bail
        if (StarRatings::$plugin->getSettings()->requireLogin) {
            return;
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

    /**
     * `$userId` can be a user ID or User Model,
     * convert it into a valid ID if necessary.
     *
     * @param int|User|null $userId
     */
    public function validateUserId(int|User|null &$userId): void
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
            } else if (is_object($userId) && ($userId instanceof User)) {
                // It's already a User model
                $user = $userId;
            }
        }

        // Get user ID, or rate anonymously
        $userId = ($user ? $user->id : null);
    }

}
