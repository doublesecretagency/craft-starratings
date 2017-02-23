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
		return null;
	}

	protected function defineSettings()
	{
		return array(
			'behavior'    => array(AttributeType::String, 'default' => 'ratable'),
			'starKey'     => array(AttributeType::String, 'default' => null),
		);
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		craft()->starRatings->backendField = true;

		// if (!$value)
		// 	$value = new StarRatings_StarRatingModel();

		// $id = craft()->templates->formatInputId($name);
		// $namespacedId = craft()->templates->namespaceInputId($id);

/* -- Include our Javascript & CSS */

		// craft()->templates->includeCssResource('starratings/css/field.css');
		// craft()->templates->includeJsResource('starratings/js/field.js');

/* -- Variables to pass down to our field.js */

		// $jsonVars = array(
		// 	'id' => $id,
		// 	'name' => $name,
		// 	'namespace' => $namespacedId,
		// 	'prefix' => craft()->templates->namespaceInputId(""),
		// );

		// $jsonVars = json_encode($jsonVars);
		// craft()->templates->includeJs("$('#{$namespacedId}-field').StarRatings_StarRatingFieldType(" . $jsonVars . ");");

/* -- Variables to pass down to our rendered template */

		$variables = array(
			// 'id' => $id,
			// 'name' => $name,
			// 'namespaceId' => $namespacedId,
			// 'values' => $value,
			'settings' => $this->getSettings(),
			'avgRating' => 2,
		);

		return craft()->templates->render('starratings/field', $variables);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('starratings/field-settings', array(
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