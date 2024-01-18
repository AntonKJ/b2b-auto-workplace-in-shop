<?php
namespace common\assets;

use yii\web\AssetBundle;
use common\assets\AngularAsset;

class AngularBootstrapAsset extends AssetBundle
{

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];

	public $sourcePath = '@bower/angular-bootstrap';

	public $js = [
		'ui-bootstrap.min.js',
		'ui-bootstrap-tpls.min.js',
	];

	public $css = [
		//'ui-bootstrap-csp.css',
	];

	public $publishOptions = [
		'only' => [

		]
	];

	public $depends = [
		AngularAsset::class,
	];

}