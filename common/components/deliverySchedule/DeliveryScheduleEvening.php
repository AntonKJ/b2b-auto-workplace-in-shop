<?php

namespace common\components\deliverySchedule;

class DeliveryScheduleEvening extends DeliveryScheduleAbstract
{

	public function getId()
	{
		return 'evening';
	}

	public function isActive(): bool
	{
		return true;
	}

	public function getTitle(): string
	{
		return 'с 18 до 24 часов';
	}

	public function getValue(): string
	{
		return '18-24';
	}

}
