<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularLoadingBarAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-loading-bar/build';

	public $js = [
		'loading-bar.min.js',
	];

	public $css = [
		'loading-bar.min.css'
	];

	public $publishOptions = [
		'only' => [

		]
	];

	public $depends = [
		AngularAsset::class,
	];

}