<?php

namespace common\components\payments;

use yii\base\ArrayableTrait;
use yii\base\Component;

abstract class PaymentAbstract extends Component implements PaymentInterface
{

	use ArrayableTrait;

	public function getReserveExtraDays(): int
	{
		return 0;
	}

	public function fields()
	{
		return [
			'id',
			'title',
		];
	}

}