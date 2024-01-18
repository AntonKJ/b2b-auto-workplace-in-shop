<?php

namespace common\assets;

use yii\web\AssetBundle;

class JGrowlAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/jgrowl';

	public $js = [
		'jquery.jgrowl.min.js',
	];

	public $css = [
		'jquery.jgrowl.min.css',
	];

	public $publishOptions = [
		'only' => [

		],
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

}