<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularUiMaskAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-ui-mask/dist';

	public $js = [
		'mask.min.js',
	];

	public $depends = [
		AngularAsset::class,
	];

}