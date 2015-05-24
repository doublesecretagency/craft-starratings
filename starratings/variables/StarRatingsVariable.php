<?php
namespace Craft;

class StarRatingsVariable
{

	private $_disabled = array();
	private $_iconsJsIncluded = false;
	private $_changeAllowedJsIncluded = false;
	private $_devModeJsIncluded = false;

	// 
	public function stars($elementId)
	{
		// Include CSS
		if (!in_array('css', $this->_disabled)) {
			if (craft()->starRatings->settings['allowFontAwesome']) {
				craft()->templates->includeCssResource('starratings/css/font-awesome.min.css');
			}
			craft()->templates->includeCssResource('starratings/css/starratings.css');
		}

		// Push star icons through to JS
		if (!$this->_iconsJsIncluded) {
			$starIcons = array(
				'starIconFull',
				'starIconHalf',
				'starIconEmpty',
			);
			if (!in_array('js', $this->_disabled)) {
				$iconJs = '';
				foreach ($starIcons as $icon) {
					$iconJs .= 'starRatings.'.$icon.' = '.json_encode(craft()->starRatings_rate->{$icon}).';'.PHP_EOL;
				}
				craft()->templates->includeJs($iconJs);
			}
			$this->_iconsJsIncluded = true;
		}

		// Set total number of available stars
		$total = craft()->starRatings->settings->maxStarsAvailable;

		// Defaults to unrated
		$userRating = 0;
		// Get user vote history
		if (craft()->starRatings->settings['requireLogin']) {
			$history = craft()->starRatings_query->userHistory();
		} else {
			$history = craft()->starRatings->anonymousHistory;
		}

		// If user already rated this element, get rating
		if (array_key_exists($elementId, $history)) {
			$userRating = $history[$elementId];
		}

		// Get average rating of element
		$avgRating = craft()->starRatings_query->avgRating($elementId);

		$halfStar = (fmod($avgRating, 1) >= 0.5);
		$halfStarNext = false;

		$html = '';
		for ($i = 1; $i <= $total; $i++) {

			$js = $this->_starJs($elementId, $i);

			$starValue = 'sr-value-'.$i;

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

			$classes  =  'sr-star';
			$classes .= ' sr-element-'.$elementId;
			$classes .= ' '.$starValue;
			$classes .= ' '.$starType;

			$html .= '<span onclick="'.$js.'" class="'.$classes.'">'.$star.'</span>';
		}

		return TemplateHelper::getRaw($html);
	}

	// 
	public function _starJs($elementId, $value, $prefix = false)
	{
		$this->_includeJs();
		return ($prefix?'javascript:':'')."starRatings.rate($elementId, $value)";
	}

	// 
	private function _includeJs()
	{
		if (!in_array('js', $this->_disabled)) {
			craft()->templates->includeJsResource('starratings/js/sizzle.js');
			craft()->templates->includeJsResource('starratings/js/superagent.js');
			craft()->templates->includeJsResource('starratings/js/starratings.js');
			// Allow Rating Change
			if (craft()->starRatings->settings['allowRatingChange'] && !$this->_changeAllowedJsIncluded) {
				craft()->templates->includeJs('starRatings.ratingChangeAllowed = true;');
				$this->_changeAllowedJsIncluded = true;
			}
			// Dev Mode
			if (craft()->config->get('devMode') && !$this->_devModeJsIncluded) {
				craft()->templates->includeJs('starRatings.devMode = true;');
				$this->_devModeJsIncluded = true;
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
		}
	}

	// ========================================================================

	// Sort by "highest rated"
	public function sort(ElementCriteriaModel $entries)
	{
		return craft()->starRatings_query->orderByAvgRating($entries);
	}

	// Customize star icons
	public function setStarIcons($starMap = array())
	{
		return craft()->starRatings_rate->setStarIcons($starMap);
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