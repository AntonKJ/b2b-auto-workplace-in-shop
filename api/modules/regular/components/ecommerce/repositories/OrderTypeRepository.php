<?php

namespace api\modules\regular\components\ecommerce\repositories;

use api\modules\regular\models\OrderType;
use common\models\DeliveryZone;
use common\models\DeliveryZoneDeliveryCity;
use common\models\OrderTypeGroupRel;
use Exception;
use myexample\ecommerce\GeoPosition;
use myexample\ecommerce\OrderTypeCollection;
use myexample\ecommerce\OrderTypeIdsByGroupsIntersectTrait;
use myexample\ecommerce\OrderTypeModelInterface;
use myexample\ecommerce\OrderTypeRepositoryInterface;
use yii\db\Expression;
use function count;
use function in_array;
use function is_array;

class OrderTypeRepository implements OrderTypeRepositoryInterface
{

	use OrderTypeIdsByGroupsIntersectTrait;

	/**
	 * @return OrderTypeCollection
	 */
	public function getOrderTypesOrderedByPriority(): OrderTypeCollection
	{

		static $orderTypes;
		if (null === $orderTypes) {

			$orderTypes = new OrderTypeCollection();

			$reader = OrderType::find()->defaultOrder();

			/** @var OrderType $orderType */
			foreach ($reader->each() as $orderType) {
				$orderTypes->add($orderType->getEcommerceEntity());
			}
		}

		return $orderTypes;
	}

	/**
	 * @param $id
	 * @param $category
	 * @return OrderTypeCollection
	 */
	public function getOrderTypesByIdByCategoryOrderedByPriority($id, $category): OrderTypeCollection
	{

		/** @var OrderTypeCollection[] $cache */
		static $cache;

		if (is_array($id)) {

			$id = array_unique($id);
			sort($id);
		}

		if (is_array($category)) {

			$category = array_unique($category);
			sort($category);
		}

		$key = md5(json_encode([$id, $category]));
		if (!isset($cache[$key])) {

			$cache[$key] = new OrderTypeCollection();

			/** @var OrderTypeModelInterface $ot */
			foreach ($this->getOrderTypesOrderedByPriority() as $ot) {

				$idArray = is_array($id);
				$categoryArray = is_array($category);

				if ((($idArray && in_array($ot->getId(), $id)) || (!$idArray && $ot->getId() == $id)) &&
					(($categoryArray && in_array($ot->getType(), $category)) || (!$categoryArray && $ot->getType() == $category))) {
					$cache[$key]->add($ot);
				}
			}
		}

		return $cache[$key];
	}

	/**
	 * @param array|int[] $groupIds
	 * @return array
	 */
	protected function fetchOrderTypeIdsByGroupsIntersect(array $groupIds): array
	{

		$query = OrderTypeGroupRel::find()
			->select(['order_type_id'])
			->andWhere(['[[group_id]]' => $groupIds]);

		if (count($groupIds) > 1) {
			$query
				->groupBy('order_type_id')
				->andHaving(new Expression('COUNT(DISTINCT [[group_id]]) = :cnt', [
					':cnt' => count($groupIds),
				]));
		}

		return $query->column();
	}

	/**
	 * @param array $orderTypeId
	 * @param GeoPosition|null $geoPosition
	 * @return OrderTypeCollection
	 */
	public function fetchOrderTypesCityRegionCategoryByIdsOrderedByPriority(array $orderTypeId, ?GeoPosition $geoPosition = null): OrderTypeCollection
	{

		$orderTypeCollection = new OrderTypeCollection();
		if ([] === $orderTypeId) {
			return $orderTypeCollection;
		}

		$deliveryCityQuery = DeliveryZoneDeliveryCity::find()
			->select([
				'delivery_zone_id',
				'COUNT(delivery_city_id) cities_cnt',
			])
			->groupBy('delivery_zone_id');

		$query = OrderType::find()
			->select('ot.*')
			->alias('ot')
			->leftJoin(['dz' => DeliveryZone::tableName()], 'dz.order_type_id = ot.ot_id')
			->leftJoin(['dzdc' => $deliveryCityQuery], 'dzdc.delivery_zone_id = dz.id')
			->andWhere([
				'ot.ot_id' => $orderTypeId,
				'ot.category' => [
					OrderTypeModelInterface::TYPE_CITY,
					OrderTypeModelInterface::TYPE_REGION,
				],
			])
			->andWhere('dzdc.cities_cnt > 0 OR ot.category = :otCategory', [
				':otCategory' => OrderTypeModelInterface::TYPE_CITY,
			])
			->orderByPriority();

		// Если передали гео-позицию, фильтруем и по ней
		if ($geoPosition instanceof GeoPosition) {

			$query
				->andWhere([
					'and',
					'dz.delivery_area IS NOT NULL',
					'ST_CONTAINS(dz.delivery_area, POINT(:lat, :lng))',
				]);

			$query->params['lat'] = $geoPosition->getLat();
			$query->params['lng'] = $geoPosition->getLng();
		}

		/** @var OrderType $orderType */
		foreach ($query->each() as $orderType) {
			$orderTypeCollection->add($orderType->getEcommerceEntity());
		}

		return $orderTypeCollection;
	}

	/**
	 * @param array $orderTypeId
	 * @return array
	 * @throws Exception
	 */
	public function getDeliveryZoneIdsWithCitiesByOrderTypeIds(array $orderTypeId): array
	{

		//todo: переписать запрос, тут нужно отталкиваться от order_types
		$deliveryCityQuery = DeliveryZoneDeliveryCity::find()
			->select([
				'delivery_zone_id',
				'COUNT(delivery_city_id) cities_cnt',
			])
			->groupBy('delivery_zone_id');

		$query = DeliveryZone::find();
		$query
			->alias('dz')
			->select(['dz.id', 'dz.order_type_id'])
			->leftJoin(['ot' => OrderType::tableName()], 'ot.ot_id = dz.order_type_id')
			->leftJoin(['dzdc' => $deliveryCityQuery], 'dzdc.delivery_zone_id = dz.id')
			->andWhere(['ot.ot_id' => $orderTypeId])
			->andWhere('dzdc.cities_cnt > 0 OR ot.category = :otCategory', [
				':otCategory' => OrderTypeModelInterface::TYPE_CITY,
			])
			->asArray();

		$deliveryZones = [];
		foreach ($query->each() as $row) {
			$deliveryZones[(int)$row['id']] = (int)$row['order_type_id'];
		}

		return $deliveryZones;
	}

}