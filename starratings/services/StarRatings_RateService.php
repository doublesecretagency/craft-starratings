<?php
namespace Craft;

class StarRatings_RateService extends BaseApplicationComponent
{

	public $starIconFull;
	public $starIconHalf;
	public $starIconEmpty;

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
	public function rate($elementId, $rating)
	{

		$alreadyRated = 'You have already rated this element.';

		// If login is required
		if (craft()->starRatings->settings['requireLogin']) {
			// Update user history
			if (!$this->_updateUserHistoryDatabase($elementId, $rating)) {
				return $alreadyRated;
			}
		} else {
			// Update user cookie
			if (!$this->_updateUserHistoryCookie($elementId, $rating)) {
				return $alreadyRated;
			}
		}
		
		// Update element average rating
		$this->_updateElementAvgRating($elementId, $rating);
		$this->_updateRatingLog($elementId, $rating);

		return array(
			'id'     => $elementId,
			'rating' => $rating,
		);

	}

	// 
	public function changeRating($elementId, $newRating, $oldRating)
	{
		$this->_removeRatingFromCookie($elementId);
		$this->_removeRatingFromDb($elementId);

		$alreadyRated = 'You have already rated this element.';

		// If login is required
		if (craft()->starRatings->settings['requireLogin']) {
			// Update user history
			if (!$this->_updateUserHistoryDatabase($elementId, $newRating)) {
				return $alreadyRated;
			}
		} else {
			// Update user cookie
			if (!$this->_updateUserHistoryCookie($elementId, $newRating)) {
				return $alreadyRated;
			}
		}

		$this->_updateElementAvgRating($elementId, $oldRating, true);
		$this->_updateElementAvgRating($elementId, $newRating);
		$this->_updateRatingLog($elementId, $newRating, true);
		return array(
			'id'     => $elementId,
			'rating' => $newRating,
		);
	}

	// 
	private function _updateUserHistoryDatabase($elementId, $rating)
	{
		$user = craft()->userSession->getUser();
		// If user is not logged in, return false
		if (!$user) {
			return false;
		}
		// Load existing element history
		$record = StarRatings_UserHistoryRecord::model()->findByPK($user->id);
		// If no history exists, create new
		if (!$record) {
			$record = new StarRatings_UserHistoryRecord;
			$record->id = $user->id;
			$history = array();
		// Else if user already rated element, return false
		} else if (array_key_exists($elementId, $record->history)) {
			return false;
		// Else, add rating to history
		} else {
			$history = $record->history;
		}
		// Register rating
		$history[$elementId] = $rating;
		$record->history = $history;
		// Save
		return $record->save();
	}

	// 
	private function _updateUserHistoryCookie($elementId, $rating)
	{
		$history =& craft()->starRatings->anonymousHistory;
		// If not already voted for, cast vote
		if (!array_key_exists($elementId, $history)) {
			$history[$elementId] = $rating;
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
	private function _updateElementAvgRating($elementId, $rating, $removingRating = false)
	{
		// Load existing element avgRating
		$record = StarRatings_ElementRatingRecord::model()->findByPK($elementId);
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
			$record->id = $elementId;
			$record->avgRating = $rating;
			$record->totalVotes = 1;
		}
		// Save
		return $record->save();
	}

	// 
	private function _updateRatingLog($elementId, $rating, $changed = false)
	{
		if (craft()->starRatings->settings['keepRatingLog']) {
			$currentUser = craft()->userSession->getUser();
			$record = new StarRatings_RatingLogRecord;
			$record->elementId = $elementId;
			$record->userId    = ($currentUser ? $currentUser->id : null);
			$record->ipAddress = $_SERVER['REMOTE_ADDR'];
			$record->ratingValue = $rating;
			$record->ratingChanged = (int) $changed;
			$record->save();
		}
	}

	// 
	private function _removeRatingFromCookie($elementId)
	{
		// Remove from cookie history
		$historyCookie =& craft()->starRatings->anonymousHistory;
		if (array_key_exists($elementId, $historyCookie)) {
			unset($historyCookie[$elementId]);
			$this->_saveUserHistoryCookie();
		}
	}

	// 
	private function _removeRatingFromDb($elementId)
	{
		$user = craft()->userSession->getUser();
		if ($user) {
			$record = StarRatings_UserHistoryRecord::model()->findByPK($user->id);
			if ($record) {
				// Remove from database history
				$historyDb = $record->history;
				if (array_key_exists($elementId, $historyDb)) {
					unset($historyDb[$elementId]);
					$record->history = $historyDb;
					$record->save();
				}
			}
		}
	}

}