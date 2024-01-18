<?php

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularNgScrollbarsAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => View::POS_END,
	];

	public $sourcePath = '@b2b/resources/vendor/ng-scrollbars';

	public $js = [
		'scrollbars.min.js',
	];

	public $depends = [
		AngularAsset::class,
		JQueryMalihuCustomScrollbarAsset::class,
	];

}