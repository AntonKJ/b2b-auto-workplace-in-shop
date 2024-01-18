<?php

namespace api\modules\regular\components\ecommerce\models;

use myexample\ecommerce\DeliveryZoneModelInterface;

class DeliveryZone implements DeliveryZoneModelInterface
{

	protected $_id;
	protected $_orderTypeId;
	protected $_deliveryArea;

	public function __construct(int $id, ?int $orderTypeId, ?array $deliveryArea)
	{
		$this->_id = $id;
		$this->_orderTypeId = $orderTypeId;
		$this->_deliveryArea = $deliveryArea;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getOrderTypeId(): ?int
	{
		return $this->_orderTypeId;
	}

	public function getDeliveryArea(): ?array
	{
		return $this->_deliveryArea;
	}

}
