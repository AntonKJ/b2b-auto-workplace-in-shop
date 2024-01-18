<?php

namespace domain\interfaces;

interface PaymentTypesInterface
{

	const TYPE_ALL = 127;

	const TYPE_CACHE = 1;
	const TYPE_INVOICE = 2;

	/**
	 * Возвращает опции дней
	 * @return array
	 */
	static public function getPaymentTypeOptions(): array;

	/**
	 * Возвращает маску
	 * @return int
	 */
	public function getPaymentTypeMask(): int;

	/**
	 * Возвращает признад осуществления доставки в день недели
	 * @return bool
	 */
	public function isPaymentTypeSet($type): bool;

	/**
	 * Возвращает массив названий дней недели в которые осуществляется доставка
	 * @return array
	 */
	public function getPaymentTypes(): array;

}