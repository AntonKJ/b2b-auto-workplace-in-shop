<?php

namespace common\assets;

use yii\web\AssetBundle;

class JsPolyfillsAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];

	public $sourcePath = '@bower/js-polyfills';

	public $js = [
		'polyfill.min.js',
	];

	public $css = [];

	public $depends = [];

}