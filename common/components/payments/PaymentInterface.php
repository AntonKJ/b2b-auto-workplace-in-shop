<?php

namespace common\components\payments;

use yii\base\Arrayable;

interface PaymentInterface extends Arrayable {

	/**
	 * Идентификатор оплаты
	 * @return string
	 */
	public static function getCode(): string;

	/**
	 * Идентификатор оплаты
	 * @return string
	 */
	public function getId(): string;

	/**
	 * Идентификатор оплаты числовое представление
	 * @return int
	 */
	public function getIdNumber(): int;

	/**
	 * Активен или нет тип оплаты
	 * @return bool
	 */
	public function isActive(): bool;

	/**
	 * Наименование типа оплаты
	 * @return string
	 */
	public function getTitle(): string;


	/**
	 * Дополнительные дни резерва
	 * @return int
	 */
	public function getReserveExtraDays(): int;

	/**
	 * Формировать счёт
	 * @return bool
	 */
	public function getIsInvoiceForm(): bool;

}