<?php

namespace common\models\query;

use common\interfaces\RegionEntityInterface;

class RegionDProducerRestrictQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param RegionEntityInterface $region
	 * @return $this
	 */
	public function byRegion(RegionEntityInterface $region): self
	{
		return $this->byRegionId($region->getId());
	}

	/**
	 * @param int $regionId
	 * @return $this
	 */
	public function byRegionId(int $regionId): self
	{
		return $this->andWhere([
			'[[region_id]]' => $regionId,
		]);
	}

	/**
	 * @param int $brandId
	 * @return $this
	 */
	public function byBrandId(int $brandId): self
	{
		return $this->andWhere([
			'[[producer_id]]' => $brandId,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\RegionDProducerRestrict[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\RegionDProducerRestrict|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

}