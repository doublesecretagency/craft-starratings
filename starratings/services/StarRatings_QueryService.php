<?php
namespace Craft;

class StarRatings_QueryService extends BaseApplicationComponent
{

	// 
	public function avgRating($elementId)
	{
		$record = StarRatings_ElementRatingRecord::model()->findByPK($elementId);
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
	public function orderByAvgRating(ElementCriteriaModel $criteria) {
		$elementIds = $this->_elementIdsByAvgRating();
		$criteria->setAttribute('id', $elementIds);
		$criteria->setAttribute('order', 'FIELD(elements.id, '.join(', ', $elementIds).')');
		return $criteria;
	}

	//
	private function _elementIdsByAvgRating() {
		$ranking = StarRatings_ElementRatingRecord::model()->findAll(array(
			'order' => 'avgRating DESC, totalVotes DESC, id ASC'
		));
		$elementIds = array();
		foreach ($ranking as $element) {
			$elementIds[] = $element->id;
		}
		return $elementIds;
	}

}