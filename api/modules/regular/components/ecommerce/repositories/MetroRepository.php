<?php

namespace api\modules\regular\components\ecommerce\repositories;

use api\modules\regular\models\MetroStation;
use myexample\ecommerce\GeoPosition;
use myexample\ecommerce\MetroRepositoryInterface;
use myexample\ecommerce\MetroStationCollection;
use myexample\ecommerce\OrderTypeModelInterface;
use myexample\ecommerce\PoiInterface;
use yii\db\ActiveQuery;

class MetroRepository implements MetroRepositoryInterface
{

	public function getByOrderTypeIdOrderedByDefault($orderTypeId): MetroStationCollection
	{
		$collection = new MetroStationCollection();

		if ($orderTypeId === []) {
			return $collection;
		}

		$query = MetroStation::find()
			->byOrderTypeId($orderTypeId)
			->defaultOrder();

		/** @var MetroStation $msModel */
		foreach ($query->each() as $msModel) {
			$collection->add($msModel->getEcommerceEntity());
		}

		return $collection;
	}

	/**
	 * @param array $metroStationsId
	 * @return array
	 */
	public function getOrderTypeIdForMetroStationsId(array $metroStationsId): array
	{

		$data = [];

		if ($metroStationsId === []) {
			return $data;
		}

		$query = MetroStation::find()
			->select(['ms.id', 'otm.order_type_id'])
			->alias('ms')
			->byId($metroStationsId)
			->innerJoinWith(['orderTypeRel' => function (ActiveQuery $q) {
				$q
					->alias('otm');
			}], false)
			->asArray();

		foreach ($query->each() as $row) {
			$data[(int)$row['id']] = (int)$row['order_type_id'];
		}

		return $data;
	}

	// PoiRepositoryInterface
	// =================================================================================================================

	/**
	 * @param array $orderTypeIds
	 * @param GeoPosition $geoPosition
	 * @param float|null $maxDistance
	 * @return PoiInterface|null
	 */
	public function getOneClosestByOrderTypeId(array $orderTypeIds, GeoPosition $geoPosition, ?float $maxDistance = null): ?PoiInterface
	{

		$data = null;
		if ([] === $orderTypeIds) {
			return $data;
		}

		$position = new \domain\entities\GeoPosition($geoPosition->getLat(), $geoPosition->getLng());

		$query = MetroStation::find()
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

		$metroStation = MetroStation::find()->byId($poi->getId())->one();

		if ($metroStation === null)
			return null;

		$query = $metroStation->getOrderType();

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