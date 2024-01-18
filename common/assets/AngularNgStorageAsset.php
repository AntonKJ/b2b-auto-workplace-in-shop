<?php
namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularNgStorageAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => View::POS_END,
	];

	public $sourcePath = '@bower/ngstorage';

	public $js = [
		YII_DEBUG ? 'ngStorage.js' : 'ngStorage.min.js',
	];

}