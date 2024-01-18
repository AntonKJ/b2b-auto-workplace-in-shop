<?php

namespace api\components;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller as ControllerBase;

class Controller extends ControllerBase
{

	public function behaviors()
	{

		$behaviors = parent::behaviors();

		if (isset($behaviors['rateLimiter']))
			unset($behaviors['rateLimiter']);

		$behaviors['authenticator'] = [
			'class' => CompositeAuth::class,
			'authMethods' => [
				HttpBasicAuth::class,
				HttpBearerAuth::class,
			],
		];

		return $behaviors;
	}

}