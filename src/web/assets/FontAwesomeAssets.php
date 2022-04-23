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

namespace doublesecretagency\starratings\web\assets;

use craft\web\AssetBundle;

/**
 * Class FontAwesomeAssets
 * @since 2.0.0
 */
class FontAwesomeAssets extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->sourcePath = '@vendor/fortawesome/font-awesome';

        $this->css = [
            'css/font-awesome.min.css',
        ];
    }

}
