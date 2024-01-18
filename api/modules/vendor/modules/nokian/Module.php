<?php

namespace api\modules\vendor\modules\nokian;

use api\components\ResponseEventHandler;
use api\modules\vendor\modules\nokian\components\Order;
use Yii;
use yii\web\Response;

class Module extends \yii\base\Module
{

	public function init()
	{
		Yii::$app->response->off(Response::EVENT_BEFORE_SEND, [ResponseEventHandler::class, 'onBeforeSend']);
		parent::init();
		Yii::configure($this, [
			'components' => [
				'order' => [
					'class' => Order::class,
					'shopMapper' => [
						'Vianor_472' => 664,

						'Vianor_296' => 665,
						'Vianor_476' => 665,

						'Vianor_292' => 666,
						'Vianor_475' => 666,
					],
				],
			],
		]);
	}

}
