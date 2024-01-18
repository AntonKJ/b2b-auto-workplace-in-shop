<?php
namespace common\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\View;

class JQueryMalihuCustomScrollbarAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => View::POS_END,
	];

	public $sourcePath = '@b2b/resources/vendor/malihu-custom-scrollbar-plugin';

	public $js = [
		YII_DEBUG ? 'jquery.mCustomScrollbar.js' : 'jquery.mCustomScrollbar.concat.min.js',
	];

	public $css = [
		YII_DEBUG ? 'jquery.mCustomScrollbar.css' : 'jquery.mCustomScrollbar.css',
	];

	public $depends = [
		JqueryAsset::class,
		JQueryMouseWheelAsset::class
	];

}