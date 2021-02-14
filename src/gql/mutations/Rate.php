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

namespace doublesecretagency\starratings\gql\mutations;

use craft\gql\base\Mutation;
use doublesecretagency\starratings\gql\arguments\RateArguments;
use doublesecretagency\starratings\gql\resolvers\RateResolver;
use GraphQL\Type\Definition\Type;

/**
 * Class Rate
 * @since 2.2.0
 */
class Rate extends Mutation
{

    /**
     * @inheritdoc
     */
    public static function getMutations(): array
    {
        return [
            'rate' => [
                'type' => Type::string(),
                'args' => RateArguments::getArguments(),
                'resolve' => RateResolver::class.'::resolve',
                'description' => 'Cast a rating on an element.'
            ],
        ];
    }

}
