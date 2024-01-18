<?php

namespace common\assets;

use yii\web\AssetBundle;

class ClipboardAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@vendor/zenorocha/clipboardjs/dist';

	public $js = [
		YII_DEBUG ? 'clipboard.js' : 'clipboard.min.js',
	];

	public $depends = [];

}