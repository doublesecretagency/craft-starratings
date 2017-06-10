<?php
namespace Craft;

class StarRatings_AvgUserRatingFieldType extends BaseFieldType implements IPreviewableFieldType
{
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return Craft::t('Average User Rating (Star Ratings)');
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
		craft()->starRatings->backendField = true;
		return craft()->templates->render('starratings/fieldtypes/avguserrating', array(
			'avgRating' => $this->_getAverage()
		));
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('starratings/fieldtypes/avguserrating-settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function getTableAttributeHtml($value)
	{
		return craft()->templates->render('starratings/fieldtypes/avguserrating-column', array(
			'avgRating' => $this->_getAverage()
		));
	}

	/**
	 * As the data leaves the database
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepValue($value)
	{
		return $this->_getAverage();
	}

	private function _getAverage()
	{
		$elementId = $this->element->id;
		$settings  = $this->getSettings();
		$starKey   = ($settings->starKey ? $settings->starKey : null);
		return craft()->starRatings_query->avgRating($elementId, $starKey);
	}
}