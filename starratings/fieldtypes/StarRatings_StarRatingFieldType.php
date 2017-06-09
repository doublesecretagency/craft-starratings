<?php
namespace Craft;

class StarRatings_StarRatingFieldType extends BaseFieldType implements IPreviewableFieldType
{
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return Craft::t('Star Rating (Star Ratings)');
	}

	/**
	 * @return mixed
	 */
	public function defineContentAttribute()
	{
		return AttributeType::Number;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		craft()->starRatings->backendField = true;
		return craft()->templates->render('starratings/fieldtypes/starrating', array(
			'name'  => $name,
			'value' => $value,
		));
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('starratings/fieldtypes/starrating-settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function getTableAttributeHtml($value)
	{
		return craft()->templates->render('starratings/fieldtypes/starrating-column', array(
			'value' => $value
		));
	}

	/**
	 * As the data enters the database
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepValueFromPost($value)
	{
		if (!is_numeric($value)) {
			$value = null;
		}
		return $value;
	}

	/**
	 * As the data leaves the database
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepValue($value)
	{
		return $value;
	}
}