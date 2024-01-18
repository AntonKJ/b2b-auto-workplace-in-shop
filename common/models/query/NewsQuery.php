<?php

namespace common\models\query;

use common\interfaces\RegionEntityInterface;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[NewsQuery]].
 *
 * @see News
 */
class NewsQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param RegionEntityInterface $region
	 * @param bool $includeNewsFormAllRegions
	 * @return NewsQuery
	 */
	public function byRegion(RegionEntityInterface $region, bool $includeNewsFormAllRegions = true)
	{
		return $this->byRegionId($region->getId(), $includeNewsFormAllRegions);
	}

	/**
	 * @param int $id
	 * @param bool $includeNewsFormAllRegions
	 * @return NewsQuery
	 */
	public function byRegionId(int $id, bool $includeNewsFormAllRegions = true)
	{

		if ($includeNewsFormAllRegions)
			$this->andWhere('[[region_id]] = :regionId OR [[region_id]] IS NULL OR [[region_id]] = 0', [
				':regionId' => $id,
			]);
		else
			$this->andWhere([
				'[[region_id]]' => $id,
			]);

		return $this;
	}

	/**
	 * @return NewsQuery
	 */
	public function defaultOrder()
	{
		return $this
			->addOrderBy(new Expression('([[region_id]] IS NOT NULL AND [[region_id]] > 0) DESC'))
			->addOrderBy([
			'[[ord_num]]' => SORT_ASC,
			'[[news_id]]' => SORT_DESC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return NewsQuery[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return NewsQuery|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
