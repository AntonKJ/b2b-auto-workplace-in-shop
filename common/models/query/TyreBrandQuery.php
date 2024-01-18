<?php

namespace common\models\query;

use common\models\TyreBrand;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[TyreBrand]].
 *
 * @see TyreBrand
 */
class TyreBrandQuery extends \yii\db\ActiveQuery
{

	public function byId($ids)
	{

		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[id]]" => $ids,
		]);
	}

	public function byUrl($url)
	{

		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[url]]" => $url,
		]);
	}

	public function byCode($code)
	{

		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[code]]" => $code,
		]);
	}

	/**
	 * @param $userId
	 * @return TyreBrandQuery
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
			}], false)
			->andWhere(['bhu.id' => null]);
	}

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
	 * @return TyreBrandQuery
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
			"{$alias}.[[Position]]" => SORT_ASC,
			"{$alias}.[[name]]" => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return TyreBrand[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return TyreBrand|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
