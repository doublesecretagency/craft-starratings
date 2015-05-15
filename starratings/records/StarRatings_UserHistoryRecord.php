<?php
namespace Craft;

class StarRatings_UserHistoryRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'starratings_userhistories';
	}

	protected function defineAttributes()
	{
		return array(
			'history' => AttributeType::Mixed,
		);
	}

	public function defineRelations()
	{
		return array(
			'user' => array(static::BELONGS_TO, 'UserRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}

}