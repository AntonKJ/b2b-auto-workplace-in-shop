<?php

namespace common\models\query;

use common\interfaces\OrderTypeGroupableInterface;
use domain\entities\GeoPosition;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[\common\models\OrderType]].
 *
 * @see \common\models\OrderType
 */
class OrderTypeQuery extends \yii\db\ActiveQuery
{

	public function addSelectAreaAsJson()
	{

		$alias = $this->getAlias();

		if (!is_array($this->select) || [] === $this->select)
			$this->addSelect(["{$alias}.*"]);

		return $this->addSelect([new Expression("ST_AsGeoJSON({$alias}.[[region_area]]) AS region_area")]);
	}

	/**
	 * @param $id array|integer
	 */
	public function byId($id)
	{
		$alias = $this->getAlias();
		return $this
			->andWhere(["{$alias}.[[ot_id]]" => $id]);
	}

	/**
	 * @param float $lat
	 * @param float $lng
	 */
	public function byCoords(float $lat, float $lng)
	{
		$alias = $this->getAlias();
		return $this
			->andWhere("{$alias}.[[region_area]] IS NOT NULL")
			->andWhere("ST_CONTAINS({$alias}.[[region_area]], POINT(:lat, :lng))", [
				':lat' => $lat,
				':lng' => $lng,
			]);
	}

	/**
	 * @param GeoPosition $position
	 */
	public function byGeoPosition(GeoPosition $position)
	{
		return $this
			->byCoords($position->getLat(), $position->getLng());
	}

	/**
	 * @param $category array|string
	 */
	public function byCategory($category)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[category]]" => $category]);
	}

	public function withGroup($alias = 'otg')
	{

		$this->joinWith(['groupsRel' => function (ActiveQuery $q) use ($alias) {
			if (!empty($alias))
				$q->alias($alias);
		}]);

		return $this;
	}

	public function byGroupId(int $id)
	{
		return $this
			->withGroup('otg')
			->andWhere([
				'otg.group_id' => $id,
			]);
	}

	public function byOrderTypeGroup(OrderTypeGroupableInterface $group)
	{

		if ((int)$group->getOrderTypeGroupId() > 0) {
			$this
				->byGroupId((int)$group->getOrderTypeGroupId());
		} else
			$this->andWhere(new Expression('1=0'));

		return $this;
	}

	public function defaultOrder()
	{
		$alias = $this->getAlias();
		return $this->orderBy(["{$alias}.[[ord_num]]" => SORT_ASC]);
	}

	public function orderByPriority()
	{
		$alias = $this->getAlias();
		return $this->orderBy(["{$alias}.[[ord_num]]" => SORT_DESC]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OrderType[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OrderType|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
