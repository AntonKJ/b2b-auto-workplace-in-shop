<?php

namespace api\modules\vendor\modules\nokian\components;

use api\components\XmlParser;
use api\config\rbac\PermissionVendor;
use api\models\VendorUser;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class Controller extends \api\components\Controller
{

	/**
	 *
	 */
	public function init()
	{
		parent::init();
		Yii::$app->request->parsers = ['*' => XmlParser::class];
		Yii::$app->response->format = ['*' => Response::FORMAT_XML];
	}

	/**
	 * @return array
	 */
	public function behaviors()
	{

		$behaviors = parent::behaviors();

		$behaviors['authenticator'] = [
			'class' => HttpBasicAuth::class,
			'auth' => static function ($username, $password) {
				$user = VendorUser::findIdentityByUsername($username);
				if (null !== $user && $user->password === $password) {
					return $user;
				}
				return null;
			},
		];

		$behaviors['contentNegotiator'] = [
			'class' => ContentNegotiator::class,
			'formats' => [
				'*' => Response::FORMAT_XML,
			],
		];

		$behaviors['access'] = [
			'class' => AccessControl::class,
			'rules' => [
				[
					'allow' => true,
					'roles' => [
						PermissionVendor::NOKIAN,
					],
				],
			],
		];

		return $behaviors;
	}

}
