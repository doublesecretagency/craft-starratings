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

namespace doublesecretagency\starratings\gql\resolvers;

use craft\gql\base\Resolver;
use doublesecretagency\starratings\StarRatings;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AvgRatingResolver
 * @since 2.2.0
 */
class AvgRatingResolver extends Resolver
{

    /**
     * @inheritdoc
     */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        // Get arguments
        $elementId = $arguments['elementId'];
        $key = ($arguments['key'] ?? null);

        // Get average rating of element
        return StarRatings::$plugin->starRatings_query->avgRating($elementId, $key);
    }

}
