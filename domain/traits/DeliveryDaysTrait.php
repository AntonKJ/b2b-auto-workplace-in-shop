<?php

namespace domain\traits;

use domain\interfaces\DeliveryDaysInterface;
use yii\base\InvalidCallException;

/**
 * Trait DeliveryDays для привязки функционала расписания доставки по дням к сущностям
 * @package domain\traits
 */
trait DeliveryDaysTrait
{

	static public function getDeliveryDaysOptions(): array
	{
		return [
			DeliveryDaysInterface::DELIVERY_DAY_MON => 'Понедельник',
			DeliveryDaysInterface::DELIVERY_DAY_TUE => 'Вторник',
			DeliveryDaysInterface::DELIVERY_DAY_WED => 'Среда',
			DeliveryDaysInterface::DELIVERY_DAY_THU => 'Четверг',
			DeliveryDaysInterface::DELIVERY_DAY_FRI => 'Пятница',
			DeliveryDaysInterface::DELIVERY_DAY_SAT => 'Суббота',
			DeliveryDaysInterface::DELIVERY_DAY_SUN => 'Воскресенье',
		];
	}

	public function getDeliveryDaysMask(): int
	{
		throw new InvalidCallException('DeliveryDays::getDeliveryDaysMask must be defined!');
	}

	/**
	 * @inheritdoc
	 */
	public function isDeliveryDaysFlagSet($dayFlag): bool
	{
		return (($this->getDeliveryDaysMask() & $dayFlag) == $dayFlag);
	}

	/*	public function setDeliveryDaysFlag($flag, bool $value)
		{
			if ($value) {
				$this->delivery_days |= $flag;
			} else {
				$this->delivery_days &= ~$flag;
			}
			return $this;
		}*/

	/**
	 * @inheritdoc
	 */
	public function getDeliveryDays(): array
	{

		$daysOptions = static::getDeliveryDaysOptions();

		$days = [];
		foreach (array_keys($daysOptions) as $i => $mask)
			if ($this->isDeliveryDaysFlagSet($mask))
				$days[$i + 1] = $daysOptions[$mask];

		return $days;
	}

	/**
	 * Возвращает ближайший день доставки
	 * @param int $day
	 * @return int
	 */
	public function getClosestDeliveryDay(int $day): int
	{
		$options = array_keys(static::getDeliveryDaysOptions());
		for ($i = 0; $i < 7; $i++) {
			$nday = (new \DateTime())->modify("+{$day} days")->format('N') - 1;
			if ($this->isDeliveryDaysFlagSet($options[$nday])) {
				break;
			}
			$day++;
		}
		return $day;
	}

}
