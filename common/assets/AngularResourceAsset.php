<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularResourceAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-resource';

	public $js = [
		'angular-resource.min.js',
	];

	public $depends = [
		AngularAsset::class,
	];

}