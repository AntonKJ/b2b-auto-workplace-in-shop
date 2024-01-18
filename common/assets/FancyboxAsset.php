<?php

namespace common\assets;

use yii\web\AssetBundle;

class FancyboxAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@npm/fancybox/dist';

	public $js = [
		'js/jquery.fancybox.pack.js',
	];

	public $css = [
		'css/jquery.fancybox.css',
	];

	public $publishOptions = [
		'only' => [

		],
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

}