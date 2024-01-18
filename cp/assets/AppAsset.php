<?php

namespace cp\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;
use yii\bootstrap\BootstrapAsset;
use common\assets\FontAwesomeAsset;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [

    ];

    public $js = [

    ];

    public $depends = [
	    YiiAsset::class,
	    BootstrapAsset::class,
    ];
}
