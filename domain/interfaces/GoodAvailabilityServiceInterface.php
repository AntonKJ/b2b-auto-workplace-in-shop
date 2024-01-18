<?php

namespace domain\interfaces;

use common\interfaces\RegionEntityInterface;

interface GoodAvailabilityServiceInterface extends ServiceInterface
{

	public function getAvailability(GoodEntityInterface $good, RegionEntityInterface $region): array;

	public function getRealAvailability($goodId, int $zoneId): array;

	public function getOrderTypes(): array;

	public function getOrderTypeStock($goodId, int $zoneId): array;

}