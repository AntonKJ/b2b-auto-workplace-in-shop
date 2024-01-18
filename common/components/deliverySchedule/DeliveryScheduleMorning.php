<?php

namespace common\components\deliverySchedule;

class DeliveryScheduleMorning extends DeliveryScheduleAbstract
{

	public function getId()
	{
		return 'morning';
	}

	public function isActive(): bool
	{
		return true;
	}

	public function getTitle(): string
	{
		return 'с 9 до 18 часов';
	}

	public function getValue(): string
	{
		return '9-18';
	}

}
