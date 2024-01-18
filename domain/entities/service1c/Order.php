<?php

namespace domain\entities\service1c;

use DateTimeImmutable;
use domain\entities\EntityBase;

class Order extends EntityBase
{

	public const STATUS_ASSEMBLE = 10; // Собирается
	public const STATUS_RESERVED = 20; // Зарезервирован
	public const STATUS_RESERVE_CANCEL = 30; // Снят с резерва
	public const STATUS_PREPARED_FOR_DELIVERY = 35; // Подготовлен к доставке
	public const STATUS_TO_DELIVERY_SERVICE = 40; // Передан в службу доставки
	public const STATUS_TO_COURIER = 50; // Передан курьеру
	public const STATUS_SHIPPED = 60; // Отгружен
	public const STATUS_DELIVERED = 70; // Доставлен

	public const PAYMENT_STATUS_UNPAID = 10;
	public const PAYMENT_STATUS_PAID = 20;

	public const PAYMENT_FORM_CASH = 10;
	public const PAYMENT_FORM_INVOICE = 20;

	protected $number;
	protected $priceTotal;

	protected $orderStatus;

	protected $paymentStatus;
	protected $paymentForm;

	protected $invoiceNumber;

	/**
	 * @var DateTimeImmutable
	 */
	protected $orderDate;

	/**
	 * @var DateTimeImmutable|null
	 */
	protected $endDateReserve;

	protected $store;
	protected $numberTTN;
	/**
	 * @var boolean
	 */
	protected $delivery;
	/**
	 * @var string|null
	 */
	protected $deliveryAddress;
	/**
	 * @var DateTimeImmutable|null
	 */
	protected $deliveryDate;
	/**
	 * @var DateTimeImmutable|null
	 */
	protected $clientDeliveryDate;

	protected $commentClient;
	protected $commentMoving;

	protected $goods;

	protected $saleNumbers;

	protected $clientCode;
	protected $clientName;
	protected $phone;

	/**
	 * @return mixed
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @return mixed
	 */
	public function getPriceTotal()
	{
		return $this->priceTotal;
	}

	/**
	 * @return mixed
	 */
	static public function getOrderStatusOptions()
	{

		return [
			static::STATUS_ASSEMBLE => 'Собирается',
			static::STATUS_RESERVED => 'Зарезервирован',
			static::STATUS_RESERVE_CANCEL => 'Снят с резерва',
			static::STATUS_PREPARED_FOR_DELIVERY => 'Подготовлен к доставке',
			static::STATUS_TO_DELIVERY_SERVICE => 'Передан в службу доставки',
			static::STATUS_TO_COURIER => 'Передан курьеру',
			static::STATUS_SHIPPED => 'Отгружен',
			static::STATUS_DELIVERED => 'Доставлен',
		];
	}

	/**
	 * @return mixed
	 */
	public function getOrderStatusText()
	{
		$options = static::getOrderStatusOptions();
		return $options[$this->orderStatus] ?? sprintf('Неизвестный статус `%s`!', $this->orderStatus);
	}

	/**
	 * @return integer
	 */
	public function getOrderStatus()
	{
		return $this->orderStatus;
	}

	/**
	 * @return string
	 */
	public function getPaymentStatusText()
	{

		if (empty($this->paymentStatus))
			return null;

		$options = static::getPaymentStatusOptions();
		return $options[$this->paymentStatus] ?? sprintf('Неизвестный статус оплаты `%s`!', $this->paymentStatus);
	}

	/**
	 * @return bool
	 */
	public function isPaid()
	{
		return $this->paymentStatus == static::PAYMENT_STATUS_PAID;
	}

	/**
	 * @return array
	 */
	public static function getPaymentStatusOptions()
	{

		return [
			static::PAYMENT_STATUS_UNPAID => 'Ожидается оплата',
			static::PAYMENT_STATUS_PAID => 'Оплачено',
		];
	}

	/**
	 * @return integer
	 */
	public function getPaymentStatus()
	{
		return $this->paymentStatus;
	}

	/**
	 * @return array
	 */
	static public function getPaymentFormOptions()
	{

		return [
			static::PAYMENT_FORM_CASH => 'Наличная',
			static::PAYMENT_STATUS_PAID => 'Безналичная',
		];
	}

	/**
	 * @return string
	 */
	public function getPaymentFormText()
	{

		if (empty($this->paymentForm))
			return null;

		$options = static::getPaymentFormOptions();
		return $options[$this->paymentForm] ?? sprintf('Неизвестная форма оплаты `%s`!', $this->paymentForm);
	}

	/**
	 * @return mixed
	 */
	public function getPaymentForm()
	{
		return $this->paymentForm;
	}

	/**
	 * @return mixed
	 */
	public function getInvoiceNumber()
	{
		return $this->invoiceNumber;
	}

	/**
	 * @return mixed
	 */
	public function getStore()
	{
		return $this->store;
	}

	/**
	 * @return mixed
	 */
	public function getNumberTTN()
	{
		return $this->numberTTN;
	}

	/**
	 * @return mixed
	 */
	public function getDelivery()
	{
		return $this->delivery;
	}

	/**
	 * @return mixed
	 */
	public function getDeliveryAddress()
	{
		return $this->deliveryAddress;
	}

	/**
	 * @return mixed
	 */
	public function getCommentClient()
	{
		return $this->commentClient;
	}

	/**
	 * @return mixed
	 */
	public function getCommentMoving()
	{
		return $this->commentMoving;
	}

	/**
	 * @return mixed
	 */
	public function getGoods()
	{
		return $this->goods;
	}

	/**
	 * @return mixed
	 */
	public function getSaleNumbers()
	{
		return $this->saleNumbers;
	}

	/**
	 * @return mixed
	 */
	public function getClientCode()
	{
		return $this->clientCode;
	}

	/**
	 * @return mixed
	 */
	public function getClientName()
	{
		return $this->clientName;
	}

	/**
	 * @return mixed
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getOrderDate(): DateTimeImmutable
	{
		return $this->orderDate;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getEndDateReserve()
	{
		return $this->endDateReserve;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getDeliveryDate()
	{
		return $this->deliveryDate;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getClientDeliveryDate(): ?DateTimeImmutable
	{
		return $this->clientDeliveryDate;
	}

	public function fields()
	{
		return [

			'number' => $this->getNumber(),
			'priceTotal' => $this->getPriceTotal(),

			'orderStatus' => $this->getOrderStatus(),
			'orderStatusText' => $this->getOrderStatusText(),

			'paymentStatus' => $this->getPaymentStatus(),
			'paymentStatusText' => $this->getPaymentStatusText(),

			'paymentForm' => $this->getPaymentForm(),
			'paymentFormText' => $this->getPaymentFormText(),

			'invoiceNumber' => $this->getInvoiceNumber(),
			'orderDate' => $this->getOrderDate()->getTimestamp(),
			'endDateReserve' => null !== $this->getEndDateReserve() ? $this->getEndDateReserve()->getTimestamp() : null,

			'store' => $this->getStore(),
			'numberTTN' => $this->getNumberTTN(),

			'delivery' => $this->getDelivery(),
			'deliveryAddress' => $this->getDeliveryAddress(),
			'deliveryDate' => null !== $this->getDeliveryDate() ? $this->getDeliveryDate()->getTimestamp() : null,
			'clientDeliveryDate' => null !== $this->getClientDeliveryDate() ? $this->getClientDeliveryDate()->getTimestamp() : null,

			'commentClient' => $this->getCommentClient(),
			'commentMoving' => $this->getCommentMoving(),

			'goods' => $this->getGoods(),
			'saleNumbers' => $this->getSaleNumbers(),

			'clientCode' => $this->getClientCode(),
			'clientName' => $this->getClientName(),
			'phone' => $this->getPhone(),

		];
	}

}
