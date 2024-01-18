<?php

namespace domain\interfaces;

interface DeliveryDaysInterface
{

	const DELIVERY_DAYS_ALL = 127;

	const DELIVERY_DAY_SUN = 1;
	const DELIVERY_DAY_MON = 2;
	const DELIVERY_DAY_TUE = 4;
	const DELIVERY_DAY_WED = 8;
	const DELIVERY_DAY_THU = 16;
	const DELIVERY_DAY_FRI = 32;
	const DELIVERY_DAY_SAT = 64;

	/**
	 * Возвращает опции дней
	 * @return array
	 */
	static public function getDeliveryDaysOptions(): array;

	/**
	 * Возвращает маску
	 * @return int
	 */
	public function getDeliveryDaysMask(): int;

	/**
	 * Возвращает признад осуществления доставки в день недели
	 * @return bool
	 */
	public function isDeliveryDaysFlagSet($dayFlag): bool;

	/**
	 * Возвращает массив названий дней недели в которые осуществляется доставка
	 * @return array
	 */
	public function getDeliveryDays(): array;

	/**
	 * Возвращает ближайший день доставки
	 * @param int $day
	 * @return int
	 */
	public function getClosestDeliveryDay(int $day): int;


}