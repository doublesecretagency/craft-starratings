<?php
namespace Craft;

class StarRatings_RateService extends BaseApplicationComponent
{

	public $starIconFull;
	public $starIconHalf;
	public $starIconEmpty;

	public $alreadyRatedMessage = 'You have already rated this element.';

	//
	public function init()
	{
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
	private function _fa($starType)
	{
		return '<i class="fa fa-'.$starType.'"></i>';
	}

	//
	public function setStarIcons($starMap = array())
	{
		foreach ($starMap as $type => $html) {
			switch ($type) {
				case 'full'  : $this->starIconFull  = $html; break;
				case 'half'  : $this->starIconHalf  = $html; break;
				case 'empty' : $this->starIconEmpty = $html; break;
			}
		}
	}

	//
	public function rate($elementId, $key, $rating)
	{
		// If login is required
		if (craft()->starRatings->settings['requireLogin']) {
			// Update user history
			if (!$this->_updateUserHistoryDatabase($elementId, $key, $rating)) {
				return $this->alreadyRatedMessage;
			}
		} else {
			// Update user cookie
			if (!$this->_updateUserHistoryCookie($elementId, $key, $rating)) {
				return $this->alreadyRatedMessage;
			}
		}

		// Update element average rating
		$this->_updateElementAvgRating($elementId, $key, $rating);
		$this->_updateRatingLog($elementId, $key, $rating);

		return array(
			'id'     => $elementId,
			'key'    => $key,
			'rating' => $rating,
		);
	}

	//
	public function changeRating($elementId, $key, $newRating, $oldRating)
	{
		$this->_removeRatingFromDb($elementId, $key);
		$this->_removeRatingFromCookie($elementId, $key);

		// If login is required
		if (craft()->starRatings->settings['requireLogin']) {
			// Update user history
			if (!$this->_updateUserHistoryDatabase($elementId, $key, $newRating)) {
				return $this->alreadyRatedMessage;
			}
		} else {
			// Update user cookie
			if (!$this->_updateUserHistoryCookie($elementId, $key, $newRating)) {
				return $this->alreadyRatedMessage;
			}
		}

		$this->_updateElementAvgRating($elementId, $key, $oldRating, true);
		$this->_updateElementAvgRating($elementId, $key, $newRating);
		$this->_updateRatingLog($elementId, $key, $newRating, true);
		return array(
			'id'     => $elementId,
			'key'    => $key,
			'rating' => $newRating,
		);
	}

	//
	private function _updateUserHistoryDatabase($elementId, $key, $rating)
	{
		$user = craft()->userSession->getUser();
		// If user is not logged in, return false
		if (!$user) {
			return false;
		}
		// Load existing element history
		$record = StarRatings_UserHistoryRecord::model()->findByPK($user->id);
		$item = craft()->starRatings->setItemKey($elementId, $key);
		// If no history exists, create new
		if (!$record) {
			$record = new StarRatings_UserHistoryRecord;
			$record->id = $user->id;
			$history = array();
		// Else if user already rated element, return false
		} else if (array_key_exists($item, $record->history)) {
			return false;
		// Else, add rating to history
		} else {
			$history = $record->history;
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
		$history =& craft()->starRatings->anonymousHistory;
		$item = craft()->starRatings->setItemKey($elementId, $key);
		// If not already voted for, cast vote
		if (!array_key_exists($item, $history)) {
			$history[$item] = $rating;
			$this->_saveUserHistoryCookie();
			return true;
		} else {
			return false;
		}

	}

	//
	private function _saveUserHistoryCookie()
	{
		$cookie   = craft()->starRatings->userCookie;
		$history  = craft()->starRatings->anonymousHistory;
		$lifespan = craft()->starRatings->userCookieLifespan;
		craft()->userSession->saveCookie($cookie, $history, $lifespan);
	}

	//
	private function _updateElementAvgRating($elementId, $key, $rating, $removingRating = false)
	{
		// Load existing element avgRating
		$record = StarRatings_ElementRatingRecord::model()->findByAttributes(array(
			'elementId' => $elementId,
			'starKey'   => $key,
		));
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
			$record = new StarRatings_ElementRatingRecord;
			$record->elementId  = $elementId;
			$record->starKey    = $key;
			$record->avgRating  = $rating;
			$record->totalVotes = 1;
		}
		// Save
		if ($record) {
			return $record->save();
		} else {
			return false;
		}
	}

	//
	private function _updateRatingLog($elementId, $key, $rating, $changed = false)
	{
		if (craft()->starRatings->settings['keepRatingLog']) {
			$currentUser = craft()->userSession->getUser();
			$record = new StarRatings_RatingLogRecord;
			$record->elementId     = $elementId;
			$record->starKey       = $key;
			$record->userId        = ($currentUser ? $currentUser->id : null);
			$record->ipAddress     = $_SERVER['REMOTE_ADDR'];
			$record->ratingValue   = $rating;
			$record->ratingChanged = (int) $changed;
			$record->save();
		}
	}

	//
	private function _removeRatingFromDb($elementId, $key)
	{
		$user = craft()->userSession->getUser();
		if ($user) {
			$record = StarRatings_UserHistoryRecord::model()->findByPK($user->id);
			if ($record) {
				// Remove from database history
				$historyDb = $record->history;
				$item = craft()->starRatings->setItemKey($elementId, $key);
				if (array_key_exists($item, $historyDb)) {
					unset($historyDb[$item]);
					$record->history = $historyDb;
					$record->save();
				}
			}
		}
	}

	//
	private function _removeRatingFromCookie($elementId, $key)
	{
		// Remove from cookie history
		$historyCookie =& craft()->starRatings->anonymousHistory;
		$item = craft()->starRatings->setItemKey($elementId, $key);
		if (array_key_exists($item, $historyCookie)) {
			unset($historyCookie[$item]);
			$this->_saveUserHistoryCookie();
		}
	}

}