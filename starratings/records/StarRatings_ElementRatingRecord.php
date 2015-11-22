<?php
namespace Craft;

class StarRatings_ElementRatingRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'starratings_elementratings';
	}

	protected function defineAttributes()
	{
		return array(
			'starKey' => AttributeType::String,
			'avgRating' => array(
				AttributeType::Number,
				'column'   => ColumnType::Decimal,
				'length'   => 5,
				'decimals' => 3,
			),
			'totalVotes' => AttributeType::Number,
		);
	}

	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}

}