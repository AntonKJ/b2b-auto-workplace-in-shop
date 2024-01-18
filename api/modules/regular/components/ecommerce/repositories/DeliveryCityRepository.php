<?php

namespace api\modules\regular\components\ecommerce\repositories;

use api\modules\regular\models\DeliveryCity;
use myexample\ecommerce\DeliveryCityCollection;
use myexample\ecommerce\DeliveryCityRepositoryInterface;
use myexample\ecommerce\GeoPosition;
use myexample\ecommerce\OrderTypeModelInterface;
use myexample\ecommerce\PoiInterface;

class DeliveryCityRepository implements DeliveryCityRepositoryInterface
{

	/**
	 * @param array|int|int[] $zoneId
	 * @return DeliveryCityCollection
	 */
	public function getByActiveByZoneIdOrderedByDefault($zoneId): DeliveryCityCollection
	{

		if (!is_array($zoneId))
			$zoneId = [$zoneId];

		$reader = DeliveryCity::find()->byDeliveryZoneId($zoneId)->defaultOrder();

		$data = new DeliveryCityCollection();

		/** @var DeliveryCity $row */
		foreach ($reader->each() as $row) {
			$data->add($row->getEcommerceEntity(), $row->getId());
		}

		return $data;
	}

	// PoiRepositoryInterface
	// =================================================================================================================

	public function getOneClosestByOrderTypeId(array $orderTypeIds, GeoPosition $geoPosition, ?float $maxDistance = null): ?PoiInterface
	{

		$data = null;
		if ([] === $orderTypeIds)
			return $data;

		$position = new \domain\entities\GeoPosition($geoPosition->getLat(), $geoPosition->getLng());

		$query = DeliveryCity::find()
			->byOrderTypeId($orderTypeIds)
			->orderByClosest($position)
			->limit(1);

		if (null !== $maxDistance)
			$query->byDistanceEqLess($position, $maxDistance);

		$data = $query->one();

		if ($data !== null)
			$data = $data->getEcommerceEntity();

		return $data;
	}

	/**
	 * @param PoiInterface $poi
	 * @param array $orderTypeIdsFilter
	 * @return OrderTypeModelInterface|null
	 */
	public function getOneOrderTypeByPoi(PoiInterface $poi, array $orderTypeIdsFilter = []): ?OrderTypeModelInterface
	{

		$deliveryCity = DeliveryCity::find()->byId($poi->getId())->one();

		if ($deliveryCity === null) {
			return null;
		}

		$query = $deliveryCity->getOrderTypes();

		if ([] !== $orderTypeIdsFilter)
			$query->byId($orderTypeIdsFilter);

		$query->orderByPriority();

		$orderType = $query->one();
		if ($orderType === null) {
			return null;
		}

		return $orderType->getEcommerceEntity();
	}

}