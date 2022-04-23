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

namespace doublesecretagency\starratings\behaviors;

use yii\base\Behavior;

/**
 * Class AvgRatingBehavior
 * @since 2.2.1
 */
class AvgRatingBehavior extends Behavior
{

    /**
     * @var float Average rating of an element.
     */
    public float $avgRating;

}
