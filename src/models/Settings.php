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

    /** @var int  $maxStarsAvailable  Number of stars for user to choose from. */
    public $maxStarsAvailable = 5;

    /** @var string  $starIncrements  Smallest fraction to render when displaying stars. */
    public $starIncrements = 'half';

    /** @var bool  $requireLogin  Whether a user is required to login to cast rating. */
    public $requireLogin = true;

    /** @var bool  $allowRatingChange  Whether users are allowed to change their rating. */
    public $allowRatingChange = true;

    /** @var bool  $allowFontAwesome  Whether to require Font Awesome resources. */
    public $allowFontAwesome = true;

    /** @var bool  $enableGql  Whether to enable GraphQL support. */
    public $enableGql = false;

    /** @var bool  $keepRatingLog  Whether to keep a detailed log of all ratings. */
    public $keepRatingLog = false;

}
