<?php

use common\components\ecommerce\CacheAdapter;
use common\components\ecommerce\CustomerB2BClient;
use common\components\ecommerce\repositories\DeliveryCityRepository;
use common\components\ecommerce\repositories\DeliveryCitySstRepository;
use common\components\ecommerce\repositories\DeliveryCityTcRepository;
use common\components\ecommerce\repositories\DeliveryZoneRepository;
use common\components\ecommerce\repositories\MetroRepository;
use common\components\ecommerce\repositories\OrderTypeRepository;
use common\components\ecommerce\repositories\ShopGroupMoveRepository;
use common\components\ecommerce\repositories\ShopRepository;
use common\components\ecommerce\repositories\ShopStockRepository;
use common\components\PsrLoggerAdapter;
use myexample\ecommerce\Availability;
use myexample\ecommerce\customers\CustomerManager;
use myexample\ecommerce\deliveries\DeliveryCityRegion;
use myexample\ecommerce\deliveries\DeliveryManager;
use myexample\ecommerce\deliveries\DeliveryPickup;
use myexample\ecommerce\deliveries\DeliveryRussia;
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

	$soapConfig = ArrayHelper::getValue(Yii::$app->params, 'ecommerce.service1c.repository.transport', null);
	$jsonConfig = ArrayHelper::getValue(Yii::$app->params, 'ecommerce.service1c.repository.transportJson', null);

	if ($soapConfig === null || !is_array($soapConfig)) {
		throw new InvalidConfigException('Soap not configured!');
	}

	$psrLogger = new PsrLoggerAdapter(Yii::getLogger());

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
		'deliveryCitySstRepository' => DeliveryCitySstRepository::class,
		'deliveryCityTcRepository' => DeliveryCityTcRepository::class,

		'cache' => static function () {
			return new CacheAdapter(Yii::$app->cacheAvailability);
		},

		'orderLogsPath' => Yii::getAlias('@runtime/order'),

		'deliveryManager' => [
			'class' => DeliveryManager::class,
			'deliveryTypes' => [
				DeliveryPickup::class,
				DeliveryCityRegion::class,
				DeliveryRussia::class,
				DeliveryRussiaTc::class,
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
					'logger' => $psrLogger,
					'wsdl' => $soapConfig['wsdl'] ?? null,
					'username' => $soapConfig['username'] ?? null,
					'password' => $soapConfig['password'] ?? null,
				],
				'transportRest' => [
					'class' => Service1cTransportRest::class,
					'logger' => $psrLogger,
					'endpointUri' => $jsonConfig['url'] ?? null,
				],
			],
		],

	]);

};
