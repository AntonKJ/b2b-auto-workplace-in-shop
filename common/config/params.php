<?php
return [

	'adminEmail' => 'admin@myexample.ru',
	'supportEmail' => 'support@myexample.ru',

	'notify.email' => [
		'send.from' => ['infoii@myexample.ru' => 'infoii'],
		'user.registration' => [
			'B2B@myexample.ru' => 'B2B Myexample',
		],
		'user.recovery' => [
			'B2B@myexample.ru' => 'B2B Myexample',
		],
		'order.create' => [
			'ii@myexample.ru' => 'Иванов Иван',
			'infoii@myexample.ru' => 'infoii',
			'internet@myexample.ru' => 'Internet',
		],
		'order.create.types' => [
			\common\models\OrderType::CATEGORY_PICKUP => [

			],
		],
	],

	'regionsInHeader' => [
		19,
		209,
		24,
		208,
		139,
	],

	'ecommerce' => [
		'service1c' => [
			'repository' => [
				'transport' => [
					'wsdl' => 'http://77.233.99.26/ut/ws/ExchangeOfSite/ExchangeOfSite.1cws?wsdl',
					'username' => 'WebObmen',
					'password' => 'nembObeW',
				],
				'transportJson' => [
					'url' => 'https://77.233.99.26/ut/hs/MyexampleSiteEx/',
				],
			],
		],
	],

];
