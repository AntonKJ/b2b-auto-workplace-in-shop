<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularUiSelectAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-ui-select/dist';

	public $js = [
		'select.js',
	];

	public $css = [
		'select.css',
	];

	public $publishOptions = [
		'only' => [

		]
	];

	public $depends = [
		AngularAsset::class,
	];

}