<?php

use api\modules\regular\components\ecommerce\CacheAdapter;
use api\modules\regular\components\ecommerce\CustomerB2BClient;
use api\modules\regular\components\ecommerce\repositories\DeliveryCityRepository;
use api\modules\regular\components\ecommerce\repositories\DeliveryCityTcRepository;
use api\modules\regular\components\ecommerce\repositories\DeliveryZoneRepository;
use api\modules\regular\components\ecommerce\repositories\MetroRepository;
use api\modules\regular\components\ecommerce\repositories\OrderTypeRepository;
use api\modules\regular\components\ecommerce\repositories\ShopGroupMoveRepository;
use api\modules\regular\components\ecommerce\repositories\ShopRepository;
use api\modules\regular\components\ecommerce\repositories\ShopStockRepository;
use myexample\ecommerce\Availability;
use myexample\ecommerce\customers\CustomerManager;
use myexample\ecommerce\deliveries\DeliveryCityRegion;
use myexample\ecommerce\deliveries\DeliveryManager;
use myexample\ecommerce\deliveries\DeliveryPickup;
use myexample\ecommerce\deliveries\DeliveryRussiaTc;
use myexample\ecommerce\Ecommerce;
use myexample\ecommerce\payments\PaymentCash;
use myexample\ecommerce\payments\PaymentInvoice;
use myexample\ecommerce\service1c\Service1c;
use myexample\ecommerce\service1c\Service1cRepository;
use myexample\ecommerce\service1c\Service1cTransportRest;
use myexample\ecommerce\service1c\Service1cTransportSoap;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 *
 */
return static function () {

	$soapConfig = ArrayHelper::getValue(Yii::$app->params, 'soap', null);
	if ($soapConfig === null || !is_array($soapConfig)) {
		throw new InvalidConfigException('Soap not configured!');
	}

	return new Ecommerce([

		'availability' => [
			'class' => Availability::class,
			'cacheKey' => 'vendor-api',
			'availabilityCacheTTL' => 300,
		],

		'timeZone' => Yii::$app->getTimeZone(),

		'dayTimeCorrectionHour' => 17,
		'dayTimeCorrectionMinute' => 0,

		'orderTypeRepository' => OrderTypeRepository::class,
		'shopStockRepository' => ShopStockRepository::class,
		'shopRepository' => ShopRepository::class,
		'shopGroupMoveRepository' => ShopGroupMoveRepository::class,
		'metroRepository' => MetroRepository::class,
		'deliveryZoneRepository' => DeliveryZoneRepository::class,
		'deliveryCityRepository' => DeliveryCityRepository::class,
		'deliveryCityTcRepository' => DeliveryCityTcRepository::class,

		/*'deliveryCitySstRepository' => DeliveryCitySstRepository::class,*/

		'cache' => static function () {
			return new CacheAdapter(Yii::$app->cacheAvailability);
		},

		'orderLogsPath' => Yii::getAlias('@runtime/order'),

		'deliveryManager' => [
			'class' => DeliveryManager::class,
			'deliveryTypes' => [
				DeliveryPickup::class,
				DeliveryCityRegion::class,
				DeliveryRussiaTc::class,
				/* DeliveryRussia::class, */
			],
		],

		'customerManager' => [
			'class' => CustomerManager::class,
			'customerTypes' => [
				[
					'class' => CustomerB2BClient::class,
				],
			],
		],

		'paymentTypes' => [
			PaymentCash::getCode() => PaymentCash::class,
			PaymentInvoice::getCode() => PaymentInvoice::class,
		],

		'service1c' => [
			'class' => Service1c::class,
			'repository' => [
				'class' => Service1cRepository::class,
				'transport' => [
					'class' => Service1cTransportSoap::class,
					'wsdl' => $soapConfig['wsdl'] ?? null,
					'username' => $soapConfig['username'] ?? null,
					'password' => $soapConfig['password'] ?? null,
				],
				'transportRest' => [
					'class' => Service1cTransportRest::class,
					'endpointUri' => 'https://77.233.99.26/ut/hs/MyexampleSiteEx/',
				],
			],
		],

	]);

};
