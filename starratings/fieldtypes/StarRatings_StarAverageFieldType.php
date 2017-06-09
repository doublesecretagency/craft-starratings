<?php
namespace Craft;

class StarRatings_StarAverageFieldType extends BaseFieldType implements IPreviewableFieldType
{
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return Craft::t('Average Rating (Star Ratings)');
	}

	/**
	 * @return mixed
	 */
	public function defineContentAttribute()
	{
		return false;
	}

	protected function defineSettings()
	{
		return array(
			'starKey' => array(AttributeType::String, 'default' => null),
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

		$elementId = $this->element->id;
		$starKey   = ($settings->starKey ? $settings->starKey : null);

		return craft()->templates->render('starratings/fieldtypes/staraverage', array(
			'avgRating' => craft()->starRatings_query->avgRating($elementId, $starKey),
		));
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('starratings/fieldtypes/staraverage-settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function getTableAttributeHtml($value)
	{
		$settings = $this->getSettings();

		$elementId = $this->element->id;
		$starKey   = ($settings->starKey ? $settings->starKey : null);

		return craft()->templates->render('starratings/fieldtypes/staraverage-column', array(
			'avgRating' => craft()->starRatings_query->avgRating($elementId, $starKey),
		));
	}
}