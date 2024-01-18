<?php

namespace api\components;

use api\components\interfaces\VendorUserInterface;
use api\models\VendorOrder;
use domain\entities\service1c\OrderReserve;

class VendorOrderBuilder
{

	protected $_reserv;
	protected $_user;

	/**
	 * VendorUserBuilder constructor.
	 * @param $reserv OrderReserve
	 * @param $user VendorUserInterface
	 */
	public function __construct(OrderReserve $reserv, VendorUserInterface $user)
	{
		$this->_reserv = $reserv;
		$this->_user = $user;
	}

	/**
	 * @return VendorOrder
	 */
	public function create()
	{

		$vendorOrder = new VendorOrder();

		$vendorOrder->vendor = $this->_user->getVendor();
		$vendorOrder->order_id = $this->_reserv->getId();

		$vendorOrder->status = VendorOrder::STATUS_IN_RESERVE;

		return $vendorOrder;
	}

}