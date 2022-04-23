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

namespace doublesecretagency\starratings\events;

use yii\base\Event;

/**
 * Class RateEvent
 * @since 2.0.0
 */
class RateEvent extends Event
{

    /** @var int|null The element ID for the item being rated. */
    public ?int $id = null;

    /** @var string|null An optional key. */
    public ?string $key = null;

    /** @var int|null The value of the rating. */
    public ?int $rating = null;

    /** @var int|null The previous rating value (if it exists). */
    public ?int $changedFrom = null;

    /** @var int|null ID of user who cast rating (null if anonymous). */
    public ?int $userId = null;

}
