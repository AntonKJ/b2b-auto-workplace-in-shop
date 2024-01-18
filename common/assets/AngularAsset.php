<?php

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\View;

class AngularAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => View::POS_END,
	];

	public $sourcePath = '@b2b/resources/vendor/angular';

	public $js = [
		YII_DEBUG ? 'angular.js' : 'angular.min.js',
	];

	public $publishOptions = [
		'only' => [
		],
	];

	public $depends = [
		JqueryAsset::class,
	];

}