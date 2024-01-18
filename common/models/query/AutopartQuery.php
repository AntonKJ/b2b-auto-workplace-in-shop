<?php

namespace common\models\query;

use common\interfaces\RegionEntityInterface;
use common\models\Autopart;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\Autopart]].
 *
 * @see \common\models\Autopart
 */
class AutopartQuery extends ActiveQuery
{

	/**
	 * @param $id
	 * @return AutopartQuery|ActiveQuery
	 */
	public function byId($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.autopart_id" => $id]);
	}

	/**
	 * @param $categoryId
	 * @return AutopartQuery|ActiveQuery
	 */
	public function byApCategoryId($categoryId)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.apcategory_id" => $categoryId]);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @param string $alias
	 * @return AutopartQuery|ActiveQuery
	 */
	public function withPricesByRegion(RegionEntityInterface $region, $alias = 'zp')
	{
		return $this->innerJoinWith([
			'zonePrice' => static function (ZonePriceQuery $q) use ($region, $alias) {
				$q
					->alias($alias)
					->byRegionZonePrice($region)
					->byAvailability();
			},
		]);
	}

	/**
	 * @inheritdoc
	 * @return Autopart[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Autopart|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
