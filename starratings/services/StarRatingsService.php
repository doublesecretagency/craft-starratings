<?php
namespace Craft;

class StarRatingsService extends BaseApplicationComponent
{

	public $settings;

	public $userCookie = 'RatingHistory';
	public $userCookieLifespan = 315569260; // Lasts 10 years
	public $anonymousHistory;

	public $csrfIncluded = false;

	// Generate combined item key
	public function setItemKey($elementId, $key)
	{
		return $elementId.($key ? ':'.$key : '');
	}

	// Get history of anonymous user
	public function getAnonymousHistory()
	{
		$this->anonymousHistory = craft()->userSession->getStateCookieValue($this->userCookie);
		if (!$this->anonymousHistory) {
			$this->anonymousHistory = array();
			craft()->userSession->saveCookie($this->userCookie, array(), $this->userCookieLifespan);
		}
	}

	// Coming Soon
	//  - Will allow complex vote filtering,
	//    based on detailed vote log
	/*
	public function getDetailedRatings($params) {
		$params = array(
			'id' => '',
			'elementId' => '',
			'userId' => '',
			'ipAddress' => '',
			'ratingValue' => '',
			'ratingChanged' => '',
			'startDateTime' => '',
			'endDateTime' => '',
		);
	}
	*/

	// $userId can be valid user ID or UserModel
	public function validateUserId(&$userId)
	{
		// No user by default
		$user = null;

		// Handle user ID
		if (!$userId) {
			// Default to logged in user
			$user = craft()->userSession->getUser();
		} else {
			if (is_numeric($userId)) {
				// Get valid UserModel
				$user = craft()->users->getUserById($userId);
			} else if (is_object($userId) && is_a($userId, 'Craft\\UserModel')) {
				// It's already a UserModel
				$user = $userId;
			}
		}

		// Get user ID, or rate anonymously
		$userId = ($user ? $user->id : null);
	}

	// ========================================================================= //

	// Events
	/**
	 * Fires an 'onBeforeRate' event.
	 *
	 * @param Event $event
	 */
	public function onBeforeRate(Event $event)
	{
		$this->raiseEvent('onBeforeRate', $event);
	}

	/**
	 * Fires an 'onRate' event.
	 *
	 * @param Event $event
	 */
	public function onRate(Event $event)
	{
		$this->raiseEvent('onRate', $event);
	}

}