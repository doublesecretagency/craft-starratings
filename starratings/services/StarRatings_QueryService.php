<?php
namespace Craft;

class StarRatings_QueryService extends BaseApplicationComponent
{

	//
	public function fieldRating($elementId, $key)
	{

		// $record = StarRatings_ElementRatingRecord::model()->findByAttributes(array(
		// 	'elementId' => $elementId,
		// 	'starKey'   => $key,
		// ));
		// return ($record ? $record->avgRating : 0);
		return 3;
	}

	//
	public function avgRating($elementId, $key)
	{
		$record = StarRatings_ElementRatingRecord::model()->findByAttributes(array(
			'elementId' => $elementId,
			'starKey'   => $key,
		));
		return ($record ? $record->avgRating : 0);
	}

	//
	public function totalVotes($elementId, $key)
	{
		$record = StarRatings_ElementRatingRecord::model()->findByAttributes(array(
			'elementId' => $elementId,
			'starKey'   => $key,
		));
		return ($record ? $record->totalVotes : 0);
	}

	//
	public function userRating($elementId, $key, $userId = null)
	{
		// Ensure the user ID is valid
		craft()->starRatings->validateUserId($userId);

		// Defaults to unrated
		$userRating = 0;

		// Get user vote history
		if (craft()->starRatings->settings['requireLogin']) {
			$history = $this->userHistory($userId);
		} else {
			$history = craft()->starRatings->anonymousHistory;
		}

		// If user already rated this element, get rating
		$item = craft()->starRatings->setItemKey($elementId, $key);
		if (array_key_exists($item, $history)) {
			$userRating = $history[$item];
		}

		return $userRating;
	}

	//
	public function userHistory($userId = null)
	{
		if ($userId) {
			$record = StarRatings_UserHistoryRecord::model()->findByPK($userId);
			if ($record) {
				return $record->history;
			}
		}
		return array();
	}

	//
	public function orderByAvgRating(ElementCriteriaModel $criteria, $key = null)
	{
		// Collect and sort elementIds
		$elementIds = $this->_elementIdsByAvgRating($key);
		if ($elementIds) {
			// Match order of criteria to elementIds
			$criteria->setAttribute('order', 'FIELD(elements.id, '.join(', ', $elementIds).') DESC');
		}
		return $criteria;
	}

	//
	private function _elementIdsByAvgRating($key)
	{
		// Don't proceed if key isn't null, string, or numeric
		if (!is_null($key) && !is_string($key) && !is_numeric($key)) {
			return false;
		} else if (null === $key) {
			$conditions = 'starKey IS NULL';
		} else {
			$conditions = 'starKey = :key';
		}
		// Get matching ratings
		$query = craft()->db->createCommand()
			->select('elementId')
			->from('starratings_elementratings')
			->where($conditions, array(':key' => $key))
			->order('avgRating DESC, totalVotes DESC, dateUpdated DESC')
		;
		// Return elementIds
		$elementIds = $query->queryColumn();
		return array_reverse($elementIds);
	}

}