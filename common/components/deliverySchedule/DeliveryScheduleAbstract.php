<?php

namespace common\components\deliverySchedule;

use yii\base\ArrayableTrait;
use yii\base\Component;

abstract class DeliveryScheduleAbstract extends Component implements DeliveryScheduleInterface
{

	use ArrayableTrait;

	public function fields()
	{
		return [
			'id',
			'title',
		];
	}

}