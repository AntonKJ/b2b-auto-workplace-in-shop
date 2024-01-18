<?php

namespace api\modules\regular\components;

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
						PermissionVendor::REGULAR,
					],
				],
			],
		];

		return $behaviors;
	}

}