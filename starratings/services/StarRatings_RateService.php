<?php
namespace Craft;

class StarRatings_RateService extends BaseApplicationComponent
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
		return $this->setIcons($iconMap);
	}

	// ========================================================================= //

	//
	public function rate($elementId, $key, $rating, $userId = null)
	{
		// Ensure the user ID is valid
		craft()->starRatings->validateUserId($userId);

		// Get old rating by this user (if exists)
		$oldRating = craft()->starRatings_query->userRating($userId, $elementId, $key);

		// Does old rating exist, and is it different?
		$changed = ($oldRating && ($oldRating != $rating));

		// Is change allowed?
		$changeAllowed = craft()->starRatings->settings['allowRatingChange'];

		// Ensure change is allowed
		if ($changed && !$changeAllowed) {
			return $this->messageChangeDisallowed;
		}

		// Prep return data
		$returnData = array(
			'id'          => $elementId,
			'key'         => $key,
			'rating'      => $rating,
			'changedFrom' => $oldRating,
			'userId'      => $userId,
		);

		// Fire an 'onBeforeRate' event
		craft()->starRatings->onBeforeRate(new Event($this, $returnData));

		// Cast element rating. Get potential error message.
		$message = $this->_rateElement($elementId, $key, $rating, $userId, $changed, $oldRating);

		// Fire an 'onRate' event
		craft()->starRatings->onRate(new Event($this, $returnData));

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
		// If changed, remove existing rating
		if ($changed) {
			$this->_removeRatingFromDb($elementId, $key, $userId);
			$this->_removeRatingFromCookie($elementId, $key);
			$this->_updateElementAvgRating($elementId, $key, $oldRating, true);
		}

		// If login is required
		if (craft()->starRatings->settings['requireLogin']) {
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
		$record = StarRatings_UserHistoryRecord::model()->findByPK($userId);
		$item = craft()->starRatings->setItemKey($elementId, $key);
		// If no history exists, create new
		if (!$record) {
			$record = new StarRatings_UserHistoryRecord;
			$record->id = $userId;
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
	private function _updateRatingLog($elementId, $key, $rating, $userId, $changed)
	{
		if (craft()->starRatings->settings['keepRatingLog']) {
			$record = new StarRatings_RatingLogRecord;
			$record->elementId     = $elementId;
			$record->starKey       = $key;
			$record->userId        = $userId;
			$record->ipAddress     = $_SERVER['REMOTE_ADDR'];
			$record->ratingValue   = $rating;
			$record->ratingChanged = (int) $changed;
			$record->save();
		}
	}

	//
	private function _removeRatingFromDb($elementId, $key, $userId)
	{
		if ($userId) {
			$record = StarRatings_UserHistoryRecord::model()->findByPK($userId);
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