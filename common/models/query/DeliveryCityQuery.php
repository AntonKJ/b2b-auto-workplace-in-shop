<?php

namespace common\models\query;

use common\interfaces\OrderTypePoiQueryInterface;
use common\models\DeliveryCity;
use domain\entities\GeoPosition;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[DeliveryCity]].
 *
 * @see DeliveryCity
 */
class DeliveryCityQuery extends \yii\db\ActiveQuery implements OrderTypePoiQueryInterface
{

	/**
	 * Сортируем точки по удаленности
	 * @param GeoPosition $position
	 * @return $this
	 */
	public function orderByClosest(GeoPosition $position)
	{

		$alias = $this->getAlias();

		if (!\is_array($this->select) || [] === $this->select)
			$this->addSelect(["{$alias}.*"]);

		$this
			->addSelect(['distance' => new Expression("ST_Distance_Sphere(POINT({$alias}.[[lng]], {$alias}.[[lat]]), POINT(:lngDistance, :latDistance))")])
			->addParams([
				':latDistance' => $position->getLat(),
				':lngDistance' => $position->getLng(),
			])
			->orderBy('distance');

		return $this;
	}

	/**
	 * Фильтруем по удалённости
	 * @param GeoPosition $position
	 * @param float $distance в метрах
	 * @return $this
	 */
	public function byDistanceEqLess(GeoPosition $position, float $distance)
	{

		$alias = $this->getAlias();
		$this
			->andWhere(new Expression("ST_Distance_Sphere(POINT({$alias}.[[lng]], {$alias}.[[lat]]), POINT(:lngDistanceFilter, :latDistanceFilter)) <= :filterEqOrLessDistance"))
			->addParams([
				':latDistanceFilter' => $position->getLat(),
				':lngDistanceFilter' => $position->getLng(),
				':filterEqOrLessDistance' => $distance,
			]);

		return $this;

	}

	public function byDeliveryZoneId($ids)
	{

		$this->innerJoinWith(['zonesRel' => function (ActiveQuery $q) use ($ids) {
			$q
				->alias('dzr')
				->onCondition([
					'dzr.delivery_zone_id' => $ids,
				]);
		}], false);

		return $this;
	}

	public function byOrderTypeId($ids)
	{

		$this->innerJoinWith(['zones' => function (ActiveQuery $q) use ($ids) {
			$q
				->alias('dz')
				->onCondition([
					'dz.order_type_id' => $ids,
				]);
		}], false);

		return $this;
	}

	/**
	 * @param $id
	 * @return DeliveryCityQuery
	 */
	public function byId($id): DeliveryCityQuery
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.id" => $id]);
	}

	public function defaultOrder()
	{
		return $this->addOrderBy(['City' => SORT_ASC]);
	}

	/**
	 * @inheritdoc
	 * @return DeliveryCity[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return DeliveryCity|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
