<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularSanitizeAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-sanitize';

	public $js = [
		'angular-sanitize.min.js',
	];

	public $publishOptions = [
		'only' => [

		]
	];

	public $depends = [
		AngularAsset::class,
	];

}