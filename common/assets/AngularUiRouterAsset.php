<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularUiRouterAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-ui-router/release';

	public $js = [
		'angular-ui-router.min.js',
	];

	public $publishOptions = [
		'only' => [

		]
	];

	public $depends = [
		AngularAsset::class,
	];

}