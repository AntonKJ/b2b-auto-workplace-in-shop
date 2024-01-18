<?php

namespace common\models\query;

use common\interfaces\RegionEntityInterface;
use common\models\ZonePrice;

/**
 * This is the ActiveQuery class for [[\common\models\ZonePrice]].
 *
 * @see \common\models\ZonePrice
 */
class ZonePriceQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param int[]|int $ids
	 * @return $this
	 */
	public function byGoodId($ids)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[item_idx]]" => $ids,
		]);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return $this
	 */
	public function byRegionZonePrice(RegionEntityInterface $region)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[zone_id]]" => $region->getPriceZoneId()]);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return $this
	 */
	public function byRegionZone(RegionEntityInterface $region)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[zone_id]]" => $region->getZoneId()]);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return $this
	 */
	public function byRegionAltZone(RegionEntityInterface $region)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[zone_id]]" => $region->getAltZoneId()]);
	}

	/**
	 * Только доступные для продажи товары, не использовать
	 * @deprecated
	 * @return $this
	 */
	public function byAvailability()
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			'or',
			["{$alias}.[[preorder]]" => ZonePrice::PREORDER],
			['>', "{$alias}.[[total_amount]]", 0],
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ZonePrice[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ZonePrice|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
