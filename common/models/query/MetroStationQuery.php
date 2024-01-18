<?php

namespace common\models\query;

use common\interfaces\OrderTypePoiQueryInterface;
use common\models\MetroStation;
use domain\entities\GeoPosition;
use function is_array;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[MetroStation]].
 *
 * @see MetroStation
 */
class MetroStationQuery extends ActiveQuery implements OrderTypePoiQueryInterface
{

	/**
	 * @param $id
	 * @return MetroStationQuery
	 */
	public function byId($id): MetroStationQuery
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[id]]" => $id]);
	}

	/**
	 * @param int[]|int|array $ids
	 * @return $this|OrderTypePoiQueryInterface
	 */
	public function byOrderTypeId($ids)
	{

		$this->innerJoinWith(['orderTypeRel' => function (ActiveQuery $q) use ($ids) {
			$q
				->alias('otr')
				->onCondition([
					'otr.order_type_id' => $ids,
				]);
		}], false);

		return $this;
	}

	/**
	 * Сортируем точки по удаленности
	 * @param GeoPosition $position
	 * @return $this
	 */
	public function orderByClosest(GeoPosition $position)
	{

		$alias = $this->getAlias();

		if (!is_array($this->select) || [] === $this->select)
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

	public function defaultOrder()
	{
		return $this->addOrderBy([
			'title' => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return MetroStation[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return MetroStation|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
