<?php
namespace Craft;

class StarRatingsController extends BaseController
{
	protected $allowAnonymous = true;

	// Rate specified element
	public function actionRate()
	{
		$this->requireAjaxRequest();

		// Get current user & login requirement
		$currentUser   = craft()->userSession->user;
		$loginRequired = craft()->starRatings->settings['requireLogin'];

		// Check if login is required
		if ($loginRequired && !$currentUser) {

			// Return "login required" message
			$this->returnJson(craft()->starRatings_rate->messageLoginRequired);

		} else {

			// Get POST values
			$elementId = craft()->request->getPost('id');
			$key       = craft()->request->getPost('key');
			$rating    = craft()->request->getPost('rating');

			// Cast rating
			$response = craft()->starRatings_rate->rate($elementId, $key, $rating, $currentUser);
			$this->returnJson($response);

		}
	}

	// DEPRECATED: Use `actionRate` instead
	public function actionChange()
	{
		return $this->actionRate();
	}

}
