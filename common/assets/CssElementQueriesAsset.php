<?php

namespace common\assets;

use yii\web\AssetBundle;

class CssElementQueriesAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/css-element-queries/src';

	public $js = [
		'ResizeSensor.js',
		'ElementQueries.js',
	];

	public $css = [];

	public $depends = [];

}