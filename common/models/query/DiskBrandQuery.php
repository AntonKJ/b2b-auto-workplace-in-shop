<?php

namespace common\models\query;

use common\models\DiskBrand;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\DiskBrand]].
 *
 * @see \common\models\DiskBrand
 */
class DiskBrandQuery extends \yii\db\ActiveQuery
{

	public function byId($id)
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[d_producer_id]]" => $id,
		]);
	}

	public function byUrl($url)
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[code]]" => $url,
		]);
	}

	public function published()
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[is_published]]" => DiskBrand::IS_PUBLISHED,
		]);
	}


	/**
	 * @param $userId
	 * @return DiskBrandQuery
	 * @deprecated
	 */
	public function byHideFromUserId($userId)
	{

		return $this
			->joinWith(['brandsHidden' => function (ActiveQuery $q) use ($userId) {
				$q
					->alias('bhu')
					->andOnCondition([
						'bhu.user_id' => $userId,
					]);
			}])
			->andWhere(['bhu.id' => null]);
	}

	/**
	 * @param int $userId
	 * @return DiskBrandQuery
	 */
	public function byRestrictFromUserId(int $userId)
	{

		return $this
			->joinWith(['brandsRestrict' => function (ActiveQuery $q) use ($userId) {
				$q
					->alias('bhu')
					->andOnCondition([
						'bhu.opt_user_id' => $userId,
					]);
			}], false)
			->andWhere(['bhu.id' => null]);
	}

	/**
	 * @param int $regionId
	 * @return DiskBrandQuery
	 */
	public function byRestrictFromRegionId(int $regionId)
	{

		return $this
			->joinWith(['brandsRestrictByRegion' => function (ActiveQuery $q) use ($regionId) {
				$q
					->alias('bhr')
					->andOnCondition([
						'bhr.region_id' => $regionId,
					]);
			}], false)
			->andWhere(['bhr.id' => null]);
	}

	/**
	 * @return $this
	 */
	public function defaultOrder()
	{

		$alias = $this->getAlias();

		return $this->orderBy([
			"{$alias}.[[pos]]" => SORT_ASC,
			"{$alias}.[[name]]" => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskBrand[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskBrand|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
