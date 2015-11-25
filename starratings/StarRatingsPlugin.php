<?php
namespace Craft;

class StarRatingsPlugin extends BasePlugin
{

	public function init()
	{
		parent::init();
		// Plugin Settings
		craft()->starRatings->settings = $this->getSettings();
		craft()->starRatings->getAnonymousHistory();
	}

	public function getName()
	{
		return Craft::t('Star Ratings');
	}

	public function getVersion()
	{
		return '1.1.1';
	}

	public function getSchemaVersion()
	{
		return '1.1.1';
	}

	public function getDeveloper()
	{
		return 'Double Secret Agency';
	}

	public function getDeveloperUrl()
	{
		return 'https://craftpl.us/plugins/star-ratings';
		//return 'http://doublesecretagency.com';
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('starratings/_settings', array(
			'settings' => craft()->starRatings->settings
		));
	}

	protected function defineSettings()
	{
		return array(
			'maxStarsAvailable' => array(AttributeType::Number, 'default' => 5),
			'requireLogin'      => array(AttributeType::Bool,   'default' => true),
			'allowHalfStars'    => array(AttributeType::Bool,   'default' => true),
			'allowRatingChange' => array(AttributeType::Bool,   'default' => true),
			'allowFontAwesome'  => array(AttributeType::Bool,   'default' => true),
			'keepRatingLog'     => array(AttributeType::Bool,   'default' => false),
		);
	}

}
