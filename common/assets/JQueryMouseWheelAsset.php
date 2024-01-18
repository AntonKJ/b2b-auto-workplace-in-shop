<?php
namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class JQueryMouseWheelAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => View::POS_END,
	];

	public $sourcePath = '@b2b/resources/vendor/jquery-mousewheel';

	public $js = [
		YII_DEBUG ? 'jquery.mousewheel.js' : 'jquery.mousewheel.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

}