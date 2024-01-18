<?php
namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularAnimateAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => View::POS_END,
	];

	public $sourcePath = '@bower/angular-animate';

	public $js = [
		YII_DEBUG ? 'angular-animate.js' : 'angular-animate.min.js',
	];

}