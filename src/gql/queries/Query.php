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

namespace doublesecretagency\starratings\gql\queries;

use craft\gql\base\Query as BaseQuery;
use doublesecretagency\starratings\gql\arguments\QueryArguments;
use doublesecretagency\starratings\gql\resolvers\AvgRatingResolver;
use doublesecretagency\starratings\gql\resolvers\TotalVotesResolver;
use GraphQL\Type\Definition\Type;

/**
 * Class Query
 * @since 2.2.0
 */
class Query extends BaseQuery
{

    /**
     * @inheritdoc
     */
    public static function getQueries(bool $checkToken = true): array
    {
        return [
            'avgRating' => [
                'type' => Type::float(),
                'args' => QueryArguments::getArguments(),
                'resolve' => AvgRatingResolver::class.'::resolve',
                'description' => 'Get the average rating of an element.'
            ],
            'totalVotes' => [
                'type' => Type::float(),
                'args' => QueryArguments::getArguments(),
                'resolve' => TotalVotesResolver::class.'::resolve',
                'description' => 'Get the total number of ratings cast on an element.'
            ],
        ];
    }

}
