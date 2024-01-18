<?php

namespace common\components\ecommerce\models;

use myexample\ecommerce\DeliveryCitySstModelInterface;
use myexample\ecommerce\DeliveryDaysTrait;

class DeliveryCitySst implements DeliveryCitySstModelInterface
{

	use DeliveryDaysTrait;

	/**
	 * @var int
	 */
	protected $_id;
	/**
	 * @var string
	 */
	protected $_title;
	/**
	 * @var int
	 */
	protected $_zoneId;
	/**
	 * @var int
	 */
	protected $_deliveryDays;

	/**
	 * DeliveryCitySst constructor.
	 * @param int $_id
	 * @param string $_title
	 * @param int $_zoneId
	 * @param int $_deliveryDays
	 */
	public function __construct(int $_id, string $_title, int $_zoneId, int $_deliveryDays)
	{
		$this->_id = $_id;
		$this->_title = $_title;
		$this->_zoneId = $_zoneId;
		$this->_deliveryDays = $_deliveryDays;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getTitle(): string
	{
		return $this->_title;
	}

	public function getZoneId(): int
	{
		return $this->_zoneId;
	}

	public function getDeliveryDaysMask(): int
	{
		return $this->_deliveryDays;
	}

}
