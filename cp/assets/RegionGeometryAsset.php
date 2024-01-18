<?php

namespace cp\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class RegionGeometryAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@cp/resources/region-geometry/build';

	public $js = [
		YII_DEBUG ? 'region-geometry.bundle.js' : 'region-geometry.bundle.min.js',
	];

	public $css = [
		YII_DEBUG ? 'region-geometry.css' : 'region-geometry.min.css',
	];

	public $depends = [
		'cp\assets\AppAsset',
		'common\assets\AngularAsset',
		'common\assets\AngularUiRouterAsset',
		'common\assets\AngularResourceAsset',
		'common\assets\AngularSanitizeAsset',
		'common\assets\AngularI18nAsset',
		'common\assets\AngularBootstrapAsset',
		'common\assets\AngularLoadingBarAsset',
	];
}
