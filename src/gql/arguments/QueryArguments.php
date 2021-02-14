<?php
/**
 * Star Ratings plugin for Craft CMS
 *
 * An easy to use and highly flexible ratings system.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2015 Double Secret Agency
 */

namespace doublesecretagency\starratings\gql\arguments;

use craft\gql\base\Arguments;
use GraphQL\Type\Definition\Type;

/**
 * Class QueryArguments
 * @since 2.2.0
 */
class QueryArguments extends Arguments
{
    /**
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        return [
            'elementId' => [
                'name' => 'elementId',
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID of the element.'
            ],
            'key' => [
                'name' => 'key',
                'type' => Type::string(),
                'description' => 'Optional unique key.'
            ],
        ];
    }
}
