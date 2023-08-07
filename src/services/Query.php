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

use craft\base\Component;
use craft\elements\db\ElementQuery;
use doublesecretagency\starratings\models\Settings;
use doublesecretagency\starratings\StarRatings;
use doublesecretagency\starratings\records\ElementRating;
use doublesecretagency\starratings\records\UserHistory;
use yii\db\Expression;

/**
 * Class Query
 * @since 2.0.0
 */
class Query extends Component
{

    /**
     * Get the average rating of a specified element.
     *
     * @param int|null $elementId
     * @param string|null $key
     * @return float
     */
    public function avgRating(?int $elementId, ?string $key = null): float
    {
        // If no element ID, return zero
        if (!$elementId) {
            return 0;
        }

        // If key is falsey, force NULL
        if (!$key) {
            $key = null;
        }

        $record = ElementRating::findOne([
            'elementId' => $elementId,
            'starKey'   => $key,
        ]);

        return (float) ($record->avgRating ?? 0);
    }

    /**
     * Get the total number of votes for a specified element.
     *
     * @param int $elementId
     * @param string|null $key
     * @return int
     */
    public function totalVotes(int $elementId, ?string $key = null): int
    {
        $record = ElementRating::findOne([
            'elementId' => $elementId,
            'starKey'   => $key,
        ]);

        return (int) ($record->totalVotes ?? 0);
    }

    /**
     * Get the rating cast by a specific user for a specified element.
     *
     * @param int|null $userId
     * @param int $elementId
     * @param string|null $key
     * @return int
     */
    public function userRating(?int $userId, int $elementId, ?string $key = null): int
    {
        // Ensure the user ID is valid
        StarRatings::$plugin->starRatings->validateUserId($userId);

        // Defaults to unrated
        $userRating = 0;

        /** @var Settings $settings */
        $settings = StarRatings::$plugin->getSettings();

        // Get user vote history
        if ($settings->requireLogin) {
            $history = $this->userHistory($userId);
        } else {
            $history = StarRatings::$plugin->starRatings->anonymousHistory;
        }

        // If user already rated this element, get rating
        $item = StarRatings::$plugin->starRatings->setItemKey($elementId, $key);
        if (isset($history[$item])) {
            $userRating = $history[$item];
        }

        return $userRating;
    }

    /**
     * Get the rating history of a specified user.
     *
     * @param int|null $userId
     * @return array
     */
    public function userHistory(?int $userId = null): array
    {
        // If no user ID, return empty array
        if (!$userId) {
            return [];
        }

        // Get the history of a specific user
        $record = UserHistory::findOne([
            'id' => $userId,
        ]);

        // If no user record, return empty array
        if (!$record) {
            return [];
        }

        // Return user history
        return json_decode($record->history, true);
    }

    /**
     * Order element query by the average ratings of elements.
     *
     * @param ElementQuery $query
     * @param string|null $key
     * @return ElementQuery
     */
    public function orderByAvgRating(ElementQuery $query, ?string $key = null): ElementQuery
    {
        // Collect and sort elementIds
        $elementIds = $this->_elementIdsByAvgRating($key);

        // If IDs exist
        if ($elementIds) {
            // Match order to elementIds
            $ids = implode(', ', $elementIds);
            $query->orderBy = [new Expression("field([[elements.id]], {$ids}) desc")];
        }

        // Return the modified query
        return $query;
    }

    /**
     * Get all element IDs, ordered by their average ratings.
     *
     * @param string|null $key
     * @return array|null
     */
    private function _elementIdsByAvgRating(?string $key): ?array
    {
        // Don't proceed if key isn't null, string, or numeric
        if (($key !== null) && !is_string($key) && !is_numeric($key)) {
            return null;
        }

        // Adjust conditions based on whether a key was provided
        if (null === $key) {
            $conditions = 'starKey IS NULL';
        } else {
            $conditions = ['starKey' => $key];
        }

        // Get elementIds of matching ratings
        $elementIds = ElementRating::find()
            ->select('[[elementId]]')
            ->where($conditions)
            ->orderBy('[[avgRating]] desc, [[totalVotes]] desc, [[dateUpdated]] desc')
            ->column();

        // Return element IDs
        return array_reverse($elementIds);
    }

}
