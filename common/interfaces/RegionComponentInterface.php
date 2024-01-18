<?php

namespace common\interfaces;

interface RegionComponentInterface
{

	const ZONE_TYPE_WWW = 'www';
	const ZONE_TYPE_B2B = 'b2b';
	const ZONE_TYPE_CC = 'cc';

	const SPB_REGION_ID = 6;

	const MOSCOW_REGION_GROUP = [1, 19];

	public function getMoscowRegionId(): int;

	public function getRegionMoscowGroup(): array;

	public function isRegionInMoscowGroup(RegionEntityInterface $region): bool;

	public function isRegionZoneTypeWWW(RegionEntityInterface $region): bool;

	public function isRegionZoneTypeB2B(RegionEntityInterface $region): bool;

	public function isRegionZoneTypeCC(RegionEntityInterface $region): bool;

}