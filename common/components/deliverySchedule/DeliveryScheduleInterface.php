<?php

namespace common\components\deliverySchedule;

use yii\base\Arrayable;

interface DeliveryScheduleInterface extends Arrayable
{

	/**
	 * Идентификатор
	 * @return string|integer
	 */
	public function getId();

	/**
	 * Активен или нет
	 * @return bool
	 */
	public function isActive(): bool;

	/**
	 * Наименование
	 * @return string
	 */
	public function getTitle(): string;

	/**
	 * Значение для 1c
	 * @return string
	 */
	public function getValue(): string;

}
