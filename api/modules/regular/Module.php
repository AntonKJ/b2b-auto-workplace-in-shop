<?php

namespace api\modules\regular;

use api\models\VendorUserRole;
use api\modules\regular\models\ApiUser;
use common\components\auth\AuthManager;
use Yii;
use yii\web\User;

class Module extends \yii\base\Module
{
	public function init()
	{

		parent::init();

		Yii::$app->setComponents([

			'user' => [
				'class' => User::class,
				'identityClass' => ApiUser::class,
				'enableAutoLogin' => true,
				'enableSession' => false,
				'loginUrl' => null,
			],

			'authManager' => [
				'class' => AuthManager::class,
				'modelClass' => ApiUser::class,
				'defaultRoles' => [
					VendorUserRole::ROLE_GUEST,
				],
				'itemFile' => '@api/config/rbac/items.php',
			],

		]);
	}
}