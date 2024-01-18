<?php

namespace common\components\deliveries\forms;

use common\models\OptUserAddress;
use common\models\OrderType;

interface DeliveryFormInterface
{

	const JS_DATE_FORMAT = "yyyy-MM-dd'T'HH:mm:ss.SSS'Z'";

	/**
	 * @return OrderType
	 */
	public function getOrderType();

	public function getShopId();

	public function getShop();

	public function getPaymentModel();

	public function getScheduleModel();

	/**
	 * @return \DateTime
	 */
	public function getDateAsDateTime();

	public function isAllowedAddressStore(): bool;

	public function loadAddressAttributes(OptUserAddress $addressModel): void;
	
}