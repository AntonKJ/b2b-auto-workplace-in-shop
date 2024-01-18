<?php

namespace api\modules\vendor;

use Yii;
use yii\web\Response;

class Module extends \yii\base\Module
{
	public function init()
	{
		parent::init();

		Yii::configure($this, require __DIR__ . '/config.php');
		Yii::$app->response->format = Response::FORMAT_JSON;

	}
}