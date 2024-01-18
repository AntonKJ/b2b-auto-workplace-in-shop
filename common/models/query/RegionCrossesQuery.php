<?php

namespace common\models\query;

use common\models\RegionCrosses;

/**
 * This is the ActiveQuery class for [[RegionCrosses]].
 *
 * @see RegionCrosses
 */
class RegionCrossesQuery extends \yii\db\ActiveQuery
{

	public function byRegionId($regionId)
	{
		return $this->andWhere(['[[region_id]]' => $regionId]);
	}

	public function byShopId($shopId)
	{
		return $this->andWhere(['[[shop_id]]' => $shopId]);
	}

	/**
	 * @inheritdoc
	 * @return RegionCrosses[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return RegionCrosses|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
