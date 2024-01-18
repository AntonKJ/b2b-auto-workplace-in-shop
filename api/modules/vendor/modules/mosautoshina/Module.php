<?php

namespace api\modules\vendor\modules\mosautoshina;

use api\modules\vendor\modules\mosautoshina\components\address\Address;
use api\modules\vendor\modules\mosautoshina\components\Order;
use common\components\payments\PaymentCash;
use common\components\payments\PaymentInvoice;
use domain\entities\GeoPosition;
use Yii;

class Module extends \yii\base\Module
{

	public function init()
	{

		parent::init();

		Yii::configure($this, [
			'components' => [
				'order' => [
					'class' => Order::class,

					'addressList' => [
						new Address(1, 'Москва, Огородный проезд 9АС1', new GeoPosition(55.809874, 37.602939)),
						new Address(2, 'Москва, Болотниковская улица 47С1', new GeoPosition(55.661806, 37.583104)),
						new Address(3, 'Подольск, улица Быковская, 11', new GeoPosition(55.473903, 37.567770)),
						new Address(4, 'Москва, улица Бусиновская Горка, 6с1', new GeoPosition(55.877581, 37.503891)),
						new Address(5, 'Москва, улица Авиамоторная, 65с1', new GeoPosition(55.738480, 37.722406)),
					],

					'paymentMethodList' => [
						PaymentCash::getCode() => new PaymentCash(),
						PaymentInvoice::getCode() => new PaymentInvoice(),
					],
				],
			],
		]);
	}
}