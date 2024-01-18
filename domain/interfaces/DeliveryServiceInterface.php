<?php

namespace domain\interfaces;

use domain\entities\region\RegionEntityInterface;

interface DeliveryServiceInterface extends ServiceInterface
{

	const DELIVERY_ID_PEK = 1;

	public function isRegionDeliveryTypePek(RegionEntityInterface $region): bool;

}