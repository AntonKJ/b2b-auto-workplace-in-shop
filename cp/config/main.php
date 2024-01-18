<?php

$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-cp',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'cp\controllers',
	'bootstrap' => ['log'],
	'modules' => [],
	'components' => [
		/**
		 * Компонент регионов
		 * todo необходим рефакторинг после перехода на DDD
		 */
		'region' => [
			'class' => \common\components\Region::class,
			'regionServiceClass' => \domain\services\RegionService::class,
			'domainRegionVar' => 'domain',
			'defaultRegionName' => 'www',
			'moscowRegionId' => 1,
			'regionMoscowGroup' => [1, 19],
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'request' => [
			'enableCsrfValidation' => false,
		],
		'session' => [
			'name' => 'cp',
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
