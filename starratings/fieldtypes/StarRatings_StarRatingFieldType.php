<?php
namespace Craft;

class StarRatings_StarRatingFieldType extends BaseFieldType
{
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return Craft::t('Star Rating');
	}

	/**
	 * @return mixed
	 */
	public function defineContentAttribute()
	{
		return AttributeType::Number;
	}

	protected function defineSettings()
	{
		return array(
			'behavior' => array(AttributeType::String, 'default' => 'ratable'),
			'starKey'  => array(AttributeType::String, 'default' => null),
		);
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		$settings = $this->getSettings();
		craft()->starRatings->backendField = true;

		if ('showAvg' == $settings->behavior) {

			$elementId = $this->element->id;
			$starKey   = ($settings->starKey ? $settings->starKey : null);

			$variables = array(
				'avgRating' => craft()->starRatings_query->avgRating($elementId, $starKey),
			);

			return craft()->templates->render('starratings/field/avg-rating', $variables);

		} else {

			$variables = array(
				'name'  => $name,
				'value' => $value,
			);

			return craft()->templates->render('starratings/field/ratable', $variables);

		}
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('starratings/field/settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepValueFromPost($value)
	{
		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepValue($value)
	{
		return $value;
	}
}