<?php

namespace api\modules\vendor\modules\cordiant\components;

use api\config\rbac\PermissionVendor;
use yii\filters\AccessControl;

class Controller extends \api\components\Controller
{

	public function behaviors()
	{

		$behaviors = parent::behaviors();

		$behaviors['access'] = [
			'class' => AccessControl::class,
			'rules' => [
				[
					'allow' => true,
					'roles' => [
						PermissionVendor::CORDIANT,
					],
				],
			],
		];

		return $behaviors;
	}

}