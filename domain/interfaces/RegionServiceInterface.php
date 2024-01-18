<?php

namespace domain\interfaces;

use common\interfaces\RegionEntityInterface;
use domain\entities\region\RegionEntityCollectionInterface;

interface RegionServiceInterface extends ServiceInterface
{

	const ZONE_TYPE_WWW = 'www';
	const ZONE_TYPE_B2B = 'b2b';
	const ZONE_TYPE_CC = 'cc';

	const MOSCOW_REGION_ID = 1;
	const SPB_REGION_ID = 6;

	const MOSCOW_REGION_GROUP = [1, 19];

	const NO_DELIVERY_REGION_GROUP = [11, 12, 13, 14, 110];

	public function getBySlug(string $slug): RegionEntityInterface;

	public function getById(int $id): RegionEntityInterface;

	public function getAllByDeliveryTypeId(int $deliveryTypeId, ?int $zoneId = null): RegionEntityCollectionInterface;

	public function getRegionMoscowGroup(): array;

	public function getNoDeliveryRegionGroup(): array;

	public function getMoscowRegionId(): int;

	public function getSpbRegionId(): int;

	public function isRegionInMoscowGroup(RegionEntityInterface $region);

	public function isRegionZoneTypeWWW(RegionEntityInterface $region);

	public function isRegionZoneTypeB2B(RegionEntityInterface $region);

	public function isRegionZoneTypeCC(RegionEntityInterface $region);

}