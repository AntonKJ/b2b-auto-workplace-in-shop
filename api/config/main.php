<?php

use common\components\deliveries\DeliveryPickup;
use common\components\Region;
use yii\log\FileTarget;
use yii\web\Response;

$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id' => 'app-api',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'api\controllers',
	'bootstrap' => ['log'],
	'modules' => [
		'regular' => [
			'class' => \api\modules\regular\Module::class,
		],
		'vendor' => [
			'class' => \api\modules\vendor\Module::class,
		],
	],
	'components' => [

		'ecommerce' => require __DIR__ . DIRECTORY_SEPARATOR . 'ecommerce.php',

		'user' => [
			'identityClass' => \api\models\VendorUser::class,
			'enableAutoLogin' => true,
			'enableSession' => false,
			'loginUrl' => null,
		],

		'authManager' => [
			'class' => \common\components\auth\AuthManager::class,
			'modelClass' => \api\models\VendorUser::class,
			'defaultRoles' => [
				\api\models\VendorUserRole::ROLE_GUEST,
			],
			'itemFile' => '@api/config/rbac/items.php',
		],

		'response' => [
			'format' => Response::FORMAT_JSON,
		],

		'request' => [
			'enableCookieValidation' => false,
			'parsers' => [
				'application/json' => \yii\web\JsonParser::class,
				'application/xml' => \api\components\XmlParser::class,
			],
		],

		/**
		 * Компонент регионов
		 */
		'region' => [
			'class' => \common\components\Region::class,
			'regionModel' => Region::class,
			'domainRegionVar' => 'domain',
			'defaultRegionName' => 'www',
			'moscowRegionId' => 1,
			'regionMoscowGroup' => [1, 19],
		],

		/**
		 * Компонент доставки
		 */
		'delivery' => [
			'class' => \common\components\Delivery::class,
			'deliveryIdPek' => 1,
			'deliveryTypes' => [
				DeliveryPickup::class,
			],
		],

		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => FileTarget::class,
					'levels' => ['error', 'warning'],
				],
				[
					'class' => FileTarget::class,
					'logFile' => '@runtime/logs/goodyear.log',
					'levels' => ['info'],
					'categories' => [
						'vendor\goodyear\*',
					],
				],
			],
		],

		'errorHandler' => [
			'errorAction' => 'site/error',
		],

		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => require(__DIR__ . '/routes.php'),
		],

	],
	'params' => $params,
];
