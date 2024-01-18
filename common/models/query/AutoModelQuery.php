<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\AutoModel]].
 *
 * @see \common\models\AutoModel
 */
class AutoModelQuery extends \yii\db\ActiveQuery
{

	/**
	 * @inheritdoc
	 * @return AutoModelQuery
	 */
	public function models()
	{
		return $this
			->select(['brand_slug', 'model_slug', 'prod', 'model'])
			->distinct(true)
			->addOrderBy([
				'prod' => SORT_ASC,
				'model' => SORT_ASC,
			]);
	}

	/**
	 * @inheritdoc
	 * @return AutoModelQuery
	 */
	public function findById($id)
	{
		return $this->andWhere(['model_slug' => $id]);
	}

	/**
	 * @inheritdoc
	 * @return AutoModelQuery
	 */
	public function findByBrandId($id)
	{
		return $this->andWhere(['brand_slug' => $id]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoModel[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoModel|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
