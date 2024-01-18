<?php

namespace common\components\ecommerce\models;

use myexample\ecommerce\ArrayableTrait;
use myexample\ecommerce\DeliveryScheduleTrait;
use myexample\ecommerce\NextDayCorrectionTrait;
use myexample\ecommerce\OrderTypeModelInterface;
use myexample\ecommerce\payments\PaymentTypesTrait;

class OrderType implements OrderTypeModelInterface
{

	use ArrayableTrait;
	use DeliveryScheduleTrait;
	use PaymentTypesTrait;
	use NextDayCorrectionTrait;

	protected $_id;
	protected $_type;
	protected $_fromShopId;
	protected $_title;
	protected $_days;
	protected $_deliveryScheduleId;
	protected $_allowedPaytypes;
	protected $_nextdayTime;

	public function __construct(int $id, string $type, ?int $fromShopId, ?string $title, int $days, ?int $deliveryScheduleId, int $allowedPaytypes, int $nextdayTime)
	{
		$this->_id = $id;
		$this->_type = $type;
		$this->_fromShopId = $fromShopId;
		$this->_title = $title;
		$this->_days = $days;
		$this->_deliveryScheduleId = $deliveryScheduleId;
		$this->_allowedPaytypes = $allowedPaytypes;
		$this->_nextdayTime = $nextdayTime;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_id;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->_type;
	}

	/**
	 * @return int|null
	 */
	public function getFromShopId(): ?int
	{
		return $this->_fromShopId;
	}

	/**
	 * @return string|null
	 */
	public function getTitle(): ?string
	{
		return $this->_title;
	}

	/**
	 * @return int
	 */
	public function getDays(): int
	{
		return $this->_days;
	}

	/**
	 * @return int|null
	 */
	public function getDeliveryScheduleId(): ?int
	{
		return $this->_deliveryScheduleId;
	}

	public function getPaymentTypeMask(): int
	{
		return $this->_allowedPaytypes;
	}

	public function getNextDayTimeValue(): ?int
	{
		return $this->_nextdayTime;
	}

}
