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

use yii\db\Expression;

use Craft;
use craft\base\Component;
use craft\elements\db\ElementQuery;

use doublesecretagency\starratings\StarRatings;
use doublesecretagency\starratings\records\ElementRating;
use doublesecretagency\starratings\records\UserHistory;

/**
 * Class Query
 * @since 2.0.0
 */
class Query extends Component
{

    //
    public function avgRating($elementId, $key = null): float
    {
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

    //
    public function totalVotes($elementId, $key = null): int
    {
        $record = ElementRating::findOne([
            'elementId' => $elementId,
            'starKey'   => $key,
        ]);
        return (int) ($record->totalVotes ?? 0);
    }

    //
    public function userRating($userId, $elementId, $key = null)
    {
        // Ensure the user ID is valid
        StarRatings::$plugin->starRatings->validateUserId($userId);

        // Defaults to unrated
        $userRating = 0;

        // Get settings
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

    //
    public function userHistory($userId = null)
    {
        if (!$userId) {
            return [];
        }
        $record = UserHistory::findOne([
            'id' => $userId,
        ]);
        if (!$record) {
            return [];
        }
        return json_decode($record->history, true);
    }

    //
    public function orderByAvgRating(ElementQuery $query, $key = null)
    {
        // Collect and sort elementIds
        $elementIds = $this->_elementIdsByAvgRating($key);
        if ($elementIds) {
            // Match order to elementIds
            $ids = implode(', ', $elementIds);
            $query->orderBy = [new Expression("field([[elements.id]], {$ids}) desc")];
        }
        return $query;
    }

    //
    private function _elementIdsByAvgRating($key)
    {
        // Don't proceed if key isn't null, string, or numeric
        if (($key !== null) && !is_string($key) && !is_numeric($key)) {
            return false;
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
        // Return elementIds
        return array_reverse($elementIds);
    }

}
