<?php
namespace Craft;

class StarRatingsController extends BaseController
{
	protected $allowAnonymous = true;

	// Rate specified element
	public function actionRate()
	{
		$this->requireAjaxRequest();
		$loggedIn = craft()->userSession->user;
		$loginRequired = craft()->starRatings->settings['requireLogin'];
		if ($loginRequired && !$loggedIn) {
			$this->returnJson('You must be logged in to rate this element.');
		} else {
			$elementId = craft()->request->getPost('id');
			$key = craft()->request->getPost('key');
			$rating = craft()->request->getPost('rating');
			$response = craft()->starRatings_rate->rate($elementId, $key, $rating);
			$this->returnJson($response);
		}
	}

	// Change rating on specified element
	public function actionChange()
	{
		$this->requireAjaxRequest();
		$loggedIn = craft()->userSession->user;
		$loginRequired = craft()->starRatings->settings['requireLogin'];
		if ($loginRequired && !$loggedIn) {
			$this->returnJson('You must be logged in to rate this element.');
		} else if (!craft()->starRatings->settings['allowRatingChange']) {
			$this->returnJson('Unable to change rating. Rate changing is disabled.');
		} else {
			$elementId = craft()->request->getPost('id');
			$key = craft()->request->getPost('key');
			$newRating = craft()->request->getPost('rating');
			$oldRating = craft()->request->getPost('oldRating');
			$response = craft()->starRatings_rate->changeRating($elementId, $key, $newRating, $oldRating);
			$this->returnJson($response);
		}
	}

}
