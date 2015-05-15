<?php
namespace Craft;

class StarRatings_RatingLogRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'starratings_ratinglog';
	}

	protected function defineAttributes()
	{
		return array(
			'userId' => AttributeType::Number,
			'ipAddress' => AttributeType::String,
			'ratingValue' => array(
				// tinyint(2)
				AttributeType::Number,
				'column' => ColumnType::TinyInt,
				'length' => 2,
			),
			'ratingChanged' => array(
				// tinyint(1)
				AttributeType::Number,
				'column' => ColumnType::TinyInt,
				'length' => 1,
			),
		);
	}

	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}

}