<?php

namespace common\components\deliveries\forms;

use common\components\deliverySchedule\DeliveryScheduleInterface;
use yii\helpers\ArrayHelper;

/**
 * Trait DeliveryScheduleFormTrait
 * @package common\components\deliveries\forms
 */
trait DeliveryScheduleFormTrait
{

	/**
	 * @return DeliveryScheduleInterface
	 */
	public function getScheduleModel()
	{
		if ($this->schedule === null || empty($this->schedule))
			return null;

		return ArrayHelper::getValue($this->_schedules, $this->schedule, null);
	}

}