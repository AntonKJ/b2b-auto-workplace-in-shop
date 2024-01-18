<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularI18nAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-i18n';

	public $js = [
		'angular-locale_ru-ru.js',
	];

	public $publishOptions = [
		'only' => [

		]
	];

	public $depends = [
		AngularAsset::class,
	];

}