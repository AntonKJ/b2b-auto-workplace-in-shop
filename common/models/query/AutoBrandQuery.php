<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\AutoBrand]].
 *
 * @see \common\models\AutoBrand
 */
class AutoBrandQuery extends \yii\db\ActiveQuery
{

	/**
	 * @inheritdoc
	 * @return AutoBrandQuery
	 */
	public function brands()
	{
		return $this
			->select(['prod', 'brand_slug'])
			->distinct(true)
			->addOrderBy([
				'prod' => SORT_ASC,
			]);
	}

	public function findById($id)
	{
		return $this->andWhere(['brand_slug' => $id]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoBrand[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoBrand|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
