<?php

namespace api\modules\vendor\modules\cordiant;

use api\modules\vendor\modules\cordiant\components\Order;
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
				],
			],
		]);
	}
}
