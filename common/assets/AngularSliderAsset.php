<?php
namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularSliderAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => View::POS_END,
	];

	public $sourcePath = '@bower/angularjs-slider/dist';

	public $js = [
		YII_DEBUG ? 'rzslider.js' : 'rzslider.min.js',
	];

    public $css = [
        YII_DEBUG ? 'rzslider.css' : 'rzslider.min.css',
    ];

}