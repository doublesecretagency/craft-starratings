<?php
namespace Craft;

class StarRatingsVariable
{

	private $_disabled = array();

	private $_iconsJsIncluded = false;
	private $_changeAllowedJsIncluded = false;
	private $_devModeJsIncluded = false;

	// Render stars
	public function stars($elementId, $key = null, $allowElementRating = true)
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

		// Alias settings
		$settings =& craft()->starRatings->settings;

		// Include CSS
		if (!in_array('css', $this->_disabled)) {
			if ($settings['allowFontAwesome']) {
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
		$total = $settings->maxStarsAvailable;

		// Defaults to unrated
		$userRating = 0;

		// Get user vote history
		if ($settings['requireLogin']) {
			$history = craft()->starRatings_query->userHistory();
		} else {
			$history = craft()->starRatings->anonymousHistory;
		}

		// If user already rated this element, get rating
		$item = craft()->starRatings->setItemKey($elementId, $key);
		if (array_key_exists($item, $history)) {
			$userRating = $history[$item];
		}

		// Get average rating of element
		$avgRating = craft()->starRatings_query->avgRating($elementId, $key);

		$halfStar = (fmod($avgRating, 1) >= 0.5);
		$halfStarNext = false;

		$html = '';
		for ($i = 1; $i <= $total; $i++) {

			$starValue = 'sr-value-'.$i;

			if (!$userRating && ($i <= $avgRating)) {
				// Average rating
				$star = craft()->starRatings_rate->starIconFull;
				$starType = 'sr-avg-rating';
				// Determine whether a half star is next
				if ($halfStar && $settings['allowHalfStars']) {
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

			$starElement = 'sr-element-'.$elementId.($key ? '-'.$key : '');
			$classes = 'sr-star '.$starElement.' '.$starValue.' '.$starType;

			$loggedInOrAnonOk = (craft()->userSession->getUser() || !$settings['requireLogin']);
			$unratedOrReratable = (!$userRating || $settings['allowRatingChange']);
			$ratable = ($allowElementRating && $loggedInOrAnonOk && $unratedOrReratable);
			if ($ratable) {
				$classes .= ' sr-ratable';
			}

			$js = $this->_starJs($elementId, $key, $i, $allowElementRating);
			$html .= '<span onclick="'.$js.'" class="'.$classes.'">'.$star.'</span>';
		}

		return TemplateHelper::getRaw($html);
	}

	//
	public function _starJs($elementId, $key, $value, $allowElementRating, $prefix = false)
	{
		$this->_includeJs();
		$jsKey = ($key ? "'$key'" : 'null');
		$allow = ($allowElementRating ? 'true' : 'false');
		return ($prefix?'javascript:':'')."starRatings.rate($elementId, $jsKey, $value, $allow)";
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
	public function sort(ElementCriteriaModel $entries, $key = null)
	{
		return craft()->starRatings_query->orderByAvgRating($entries, $key);
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