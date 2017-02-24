<?php
namespace Craft;

class StarRatingsVariable
{

	private $_disabled = array();

	private $_cssIncluded = false;
	private $_jsIncluded  = false;

	// Render stars
	public function stars($elementId, $key = null, $allowElementRating = true, $userId = null)
	{
		// If element ID is invalid, return error
		if (!$elementId || !is_numeric($elementId)) {
			return 'Invalid element ID';
		}

		// Allow $key parameter to be skipped
		if ((null !== $key) && is_bool($key)) {
			$allowElementRating = $key;
			$key = null;
		}

		// Get ratings
		$avgRating  = craft()->starRatings_query->avgRating($elementId, $key);
		$userRating = craft()->starRatings_query->userRating($elementId, $key, $userId);

		// Draw stars
		return $this->_drawStars($avgRating, $userRating, $elementId, $key, $allowElementRating);
	}

	// Render stars
	public function starsField($elementId, $key = null, $allowElementRating = true, $userId = null)
	{
		// Get rating
		$fieldRating = craft()->starRatings_query->fieldRating($elementId, $key);

		// Draw stars
		return $this->_drawStars(0, $fieldRating);
	}

	// Render locked stars
	public function lockedStars($rating)
	{
		return $this->_drawStars($rating);
	}

	// Draw stars
	private function _drawStars($avgRating, $userRating = 0, $elementId = null, $key = null, $allowElementRating = false)
	{
		// Set total number of available stars
		$maxStarsAvailable = craft()->starRatings->settings['maxStarsAvailable'];

		$halfStar = (fmod($avgRating, 1) >= 0.5);
		$halfStarNext = false;

		$html = '';
		for ($i = 1; $i <= $maxStarsAvailable; $i++) {

			$this->_includeCss();

			// Initialize star classes
			$classes = 'sr-star sr-value-'.$i;

			// If back-end field, use larger stars
			if (craft()->starRatings->backendField) {
				$classes .= ' fa-2x';
			}

			if (!$userRating && ($i <= $avgRating)) {
				// Average rating
				$star = craft()->starRatings_rate->starIconFull;
				$starType = 'sr-avg-rating';
				// Determine whether a half star is next
				if ($halfStar && craft()->starRatings->settings['allowHalfStars']) {
					$halfStarNext = true;
				}
			} else if ($userRating && ($i <= $userRating)) {
				// User rating
				$star = craft()->starRatings_rate->starIconFull;
				$starType = 'sr-user-rating';
			} else {
				// Empty stars
				if ($halfStarNext) {
					$star = craft()->starRatings_rate->starIconHalf;
					$starType = 'sr-avg-rating';
					$halfStarNext = false;
				} else {
					$star = craft()->starRatings_rate->starIconEmpty;
					$starType = 'sr-unrated';
				}
			}
			$classes .= ' '.$starType;

			// If element specified
			if ($elementId) {
				$classes .= ' sr-element-'.$elementId.($key ? '-'.$key : '');
			} else {
				$classes .= ' sr-locked';
			}

			// If element is ratable
			$loggedInOrAnonOk = (craft()->userSession->getUser() || !craft()->starRatings->settings['requireLogin']);
			$unratedOrReratable = (!$userRating || craft()->starRatings->settings['allowRatingChange']);
			$ratable = ($elementId && $allowElementRating && $loggedInOrAnonOk && $unratedOrReratable);
			if ($ratable) {
				$classes .= ' sr-ratable';
			}

			// Append star HTML
			$onclick = 'onclick="'.$this->_starJs($elementId, $key, $i, $allowElementRating).'"';
			$html .= '<span class="'.$classes.'" '.($elementId ? $onclick : '').'>'.$star.'</span>';
		}

		// Return compiled stars
		return TemplateHelper::getRaw($html);
	}

	// Include CSS
	private function _includeCss()
	{
		// If CSS is enabled and not yet included
		if (!$this->_cssIncluded && !in_array('css', $this->_disabled)) {

			// Include CSS resources
			if (craft()->starRatings->settings['allowFontAwesome']) {
				craft()->templates->includeCssResource('starratings/css/font-awesome.min.css');
			}
			craft()->templates->includeCssResource('starratings/css/starratings.css');

			// Mark CSS as included
			$this->_cssIncluded = true;
		}
	}

	// Include JS
	private function _includeJs()
	{
		// If JS is enabled and not yet included
		if (!$this->_jsIncluded && !in_array('js', $this->_disabled)) {

			// Include JS resources
			craft()->templates->includeJsResource('starratings/js/sizzle.js');
			craft()->templates->includeJsResource('starratings/js/superagent.js');
			craft()->templates->includeJsResource('starratings/js/starratings.js');

			// Set icons
			$iconJs = '';
			$starIcons = array(
				'starIconFull',
				'starIconHalf',
				'starIconEmpty',
			);
			foreach ($starIcons as $icon) {
				$iconJs .= 'starRatings.'.$icon.' = '.json_encode(craft()->starRatings_rate->{$icon}).';'.PHP_EOL;
			}
			craft()->templates->includeJs($iconJs);

			// Allow Rating Change
			if (craft()->starRatings->settings['allowRatingChange']) {
				craft()->templates->includeJs('starRatings.ratingChangeAllowed = true;');
			}

			// Dev Mode
			if (craft()->config->get('devMode')) {
				craft()->templates->includeJs('starRatings.devMode = true;');
			}

			// CSRF
			if (craft()->config->get('enableCsrfProtection') === true) {
				if (!craft()->starRatings->csrfIncluded) {
					$csrf = '
window.csrfTokenName = "'.craft()->config->get('csrfTokenName').'";
window.csrfTokenValue = "'.craft()->request->getCsrfToken().'";
';
					craft()->templates->includeJs($csrf);
					craft()->starRatings->csrfIncluded = true;
				}
			}

			// Mark JS as included
			$this->_jsIncluded = true;
		}
	}

	// JS triggers for individual stars
	private function _starJs($elementId, $key, $value, $allowElementRating, $prefix = false)
	{
		$this->_includeJs();
		$jsKey = ($key ? "'$key'" : 'null');
		$allow = ($allowElementRating ? 'true' : 'false');
		return ($prefix?'javascript:':'')."starRatings.rate($elementId, $jsKey, $value, $allow)";
	}

	// ========================================================================

	// Output average rating of stars
	public function avgRating($elementId, $key = null)
	{
		// If element ID is invalid, log error
		if (!$elementId || !is_numeric($elementId)) {
			StarRatingsPlugin::log('Invalid element ID');
			return 0;
		}

		return craft()->starRatings_query->avgRating($elementId, $key);
	}

	// Output total votes of element
	public function totalVotes($elementId, $key = null)
	{
		// If element ID is invalid, log error
		if (!$elementId || !is_numeric($elementId)) {
			StarRatingsPlugin::log('Invalid element ID');
			return 0;
		}

		return craft()->starRatings_query->totalVotes($elementId, $key);
	}

	// ========================================================================

	// Customize icons
	public function setIcons($iconMap = array())
	{
		return craft()->starRatings_rate->setIcons($iconMap);
	}

	// DEPRECATED: Use setIcons instead
	public function setStarIcons($iconMap = array())
	{
		return $this->setIcons($iconMap);
	}

	// Sort by "highest rated"
	public function sort(ElementCriteriaModel $entries, $key = null)
	{
		return craft()->starRatings_query->orderByAvgRating($entries, $key);
	}

	// Disable native CSS and/or JS
	public function disable($resources = array())
	{
		if (is_string($resources)) {
			$resources = array($resources);
		}
		if (is_array($resources)) {
			return $this->_disabled = array_map('strtolower', $resources);
		} else {
			return false;
		}
	}

}