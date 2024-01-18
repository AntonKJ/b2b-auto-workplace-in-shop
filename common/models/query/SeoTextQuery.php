<?php

namespace common\models\query;

use common\interfaces\RegionEntityInterface;

/**
 * This is the ActiveQuery class for [[SeoTextQuery]].
 *
 * @see SeoText
 */
class SeoTextQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param string|array|string[] $keyword
	 * @return SeoTextQuery
	 */
	public function byKeyword($keyword)
	{
		return $this->andWhere([
			'keyword' => $keyword,
		]);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return SeoTextQuery
	 */
	public function byKeywordLike(string $keyword)
	{
		return $this->andWhere(['like', 'keyword', $keyword, false]);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @param bool $includeFormAllRegions
	 * @return SeoTextQuery
	 */
	public function byRegion(RegionEntityInterface $region, bool $includeFormAllRegions = true)
	{
		return $this->byRegionId($region->getId(), $includeFormAllRegions);
	}

	/**
	 * @param int $id
	 * @param bool $includeFormAllRegions
	 * @return SeoTextQuery
	 */
	public function byRegionId(int $id, bool $includeFormAllRegions = true)
	{

		if ($includeFormAllRegions)
			$this->andWhere('[[region_id]] = :regionId OR [[region_id]] IS NULL OR [[region_id]] = 0', [
				':regionId' => $id,
			]);
		else
			$this->andWhere([
				'[[region_id]]' => $id,
			]);

		return $this;
	}

	public function orderByPriority()
	{
		return $this->addOrderBy([
			'IFNULL([[region_id]], 0)' => SORT_DESC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return SeoTextQuery[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return SeoTextQuery|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
