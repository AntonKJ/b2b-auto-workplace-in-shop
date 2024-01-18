<?php

namespace common\models\query;

use common\interfaces\OrderTypeGroupableInterface;
use common\models\Region;
use common\models\ZonePrice;

/**
 * This is the ActiveQuery class for [[\common\models\DiskGood]].
 *
 * @see \common\models\DiskGood
 */
class DiskGoodQuery extends \yii\db\ActiveQuery
{

	public function byId($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[disk_id]]" => $id,
		]);
	}

	public function withPricesByRegion(Region $region, $alias = 'zp')
	{
		return $this->with(['zonePrice' => function (ZonePriceQuery $q) use ($region, $alias) {
			$q
				->alias($alias)
				->byRegionZonePrice($region);
		}]);
	}

	public function withStockByOrderTypeGroup(OrderTypeGroupableInterface $orderTypeGroup, $alias = 'otg')
	{
		return $this->with(['orderTypeStock' => function (OrderTypeStockQuery $q) use ($orderTypeGroup, $alias) {
			$q
				->alias($alias)
				->byOrderTypeGroup($orderTypeGroup);
		}]);
	}

	/**
	 * Фильтр по распродажамs
	 * @param string $zonePriceTableAlias
	 * @return $this
	 */
	public function bySales($zonePriceTableAlias = 'zpFilter')
	{

		if (!empty($zonePriceTableAlias))
			$zonePriceTableAlias .= '.';

		$this->andWhere(["{$zonePriceTableAlias}[[sale]]" => ZonePrice::SALE]);

		return $this;
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskGood[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskGood|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
