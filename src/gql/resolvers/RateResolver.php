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
 * Class RateResolver
 * @since 2.2.0
 */
class RateResolver extends Resolver
{

    /**
     * @inheritdoc
     */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        // Get arguments
        $elementId = $arguments['elementId'];
        $key = ($arguments['key'] ?? null);
        $rating = $arguments['rating'];
        $userId = ($arguments['userId'] ?? null);

        // Cast rating
        $results = StarRatings::$plugin->starRatings_rate->rate($elementId, $key, $rating, $userId);

        // If we got an error message, return it
        if (is_string($results)) {
            return $results;
        }

        // Return success message
        return 'Success';
    }

}
