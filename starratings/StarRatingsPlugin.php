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
		// Events
		if (2.3 <= craft()->getVersion()) {
			// NEW EVENT (Craft v2.3)
			craft()->on('elements.saveElement', function(Event $event) {
				craft()->starRatings->initElementTally($event->params['element']->id, $event->params['isNewElement']);
			});
		} else {
			// ORIGINAL EVENTS
			craft()->on('assets.saveAsset', function(Event $event) {
				craft()->starRatings->initElementTally($event->params['asset']->id);
			});
			craft()->on('categories.saveCategory', function(Event $event) {
				craft()->starRatings->initElementTally($event->params['category']->id, $event->params['isNewCategory']);
			});
			craft()->on('entries.saveEntry', function(Event $event) {
				craft()->starRatings->initElementTally($event->params['entry']->id, $event->params['isNewEntry']);
			});
			craft()->on('tags.saveTag', function(Event $event) {
				craft()->starRatings->initElementTally($event->params['tag']->id, $event->params['isNewTag']);
			});
			craft()->on('users.saveUser', function(Event $event) {
				craft()->starRatings->initElementTally($event->params['user']->id, $event->params['isNewUser']);
			});
		}
	}

	public function getName()
	{
		return Craft::t('Star Ratings');
	}

	public function getVersion()
	{
		return '1.1.0';
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

    public function onAfterInstall()
    {
		craft()->starRatings->initAllElementTallies();
    }
	
}
