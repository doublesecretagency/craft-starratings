<?php
namespace Craft;

class StarRatings_QueryService extends BaseApplicationComponent
{

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
	public function userHistory()
	{
		$user = craft()->userSession->getUser();
		if ($user) {
			$record = StarRatings_UserHistoryRecord::model()->findByPK($user->id);
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
			$criteria->setAttribute('order', 'FIELD(id, '.join(', ', $elementIds).') DESC');
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
			->order('avgRating desc, totalVotes desc, dateUpdated desc')
		;
		// Return elementIds
		$elementIds = $query->queryColumn();
		return array_reverse($elementIds);
	}

}