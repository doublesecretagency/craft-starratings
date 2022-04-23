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

namespace doublesecretagency\starratings\models;

use craft\base\Model;

/**
 * Class Settings
 * @since 2.0.0
 */
class Settings extends Model
{

    /**
     * @var int Number of stars for user to choose from.
     */
    public int $maxStarsAvailable = 5;

    /**
     * @var string Smallest fraction to render when displaying stars.
     */
    public string $starIncrements = 'half';

    /**
     * @var bool Whether a user is required to log in to cast rating.
     */
    public bool $requireLogin = true;

    /**
     * @var bool Whether users are allowed to change their rating.
     */
    public bool $allowRatingChange = true;

    /**
     * @var bool Whether to require Font Awesome resources.
     */
    public bool $allowFontAwesome = true;

    /**
     * @var bool Whether to enable GraphQL support.
     */
    public bool $enableGql = false;

    /**
     * @var bool Whether to keep a detailed log of all ratings.
     */
    public bool $keepRatingLog = false;

}
