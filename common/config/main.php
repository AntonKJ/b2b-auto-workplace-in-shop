<?php

use common\bootstrap\ContainerBootstrap;
use common\components\CacheAvailability;
use common\components\CacheMain;
use common\components\file\FileSystem;
use common\components\file\MediaManager;
use common\components\GlobalVar;

return [
	'vendorPath' => dirname(__DIR__, 2) . '/vendor',
	'name' => 'ml.myexample.ru - шины и диски оптом',
	'version' => '1.0',
	'language' => 'ru-RU',
	'sourceLanguage' => 'ru-RU',
	'timeZone' => 'Europe/Moscow',
	'bootstrap' => [
		ContainerBootstrap::class,
	],
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'components' => [

		'ecommerce' => require __DIR__ . DIRECTORY_SEPARATOR . 'ecommerce.php',

		'media' => [
			'class' => MediaManager::class,
			'fileSystemComponent' => [
				'class' => FileSystem::class,
				'basePath' => '/srv/www-new/htdocs',
				'baseUrl' => '//www.myexample.ru',
				'baseDomain' => 'myexample.ru',
			],
		],

		'cache' => [
			'class' => CacheMain::class,
			'useMemcached' => true,
			'servers' => [
				[
					'host' => '/var/run/memcached/memcached.sock',
				],
			],
		],

		'cacheAvailability' => [
			'class' => CacheAvailability::class,
			'useMemcached' => true,
			'servers' => [
				[
					'host' => '/var/run/memcached/memcached.sock',
				],
			],
			'keyPrefix' => '',
			'serializer' => false,
		],

		'formatter' => [
			'sizeFormatBase' => 1000,
		],

		'global' => [
			'class' => GlobalVar::class,
		],

		'assetManager' => [
			'linkAssets' => true,
			'appendTimestamp' => true,
		],

	],
];
