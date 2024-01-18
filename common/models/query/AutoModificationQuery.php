<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\AutoModification]].
 *
 * @see \common\models\AutoModification
 */
class AutoModificationQuery extends \yii\db\ActiveQuery
{

	/**
	 * @inheritdoc
	 * @return AutoModificationQuery
	 */
	public function modifications()
	{
		return $this
			->addOrderBy([
				'prod' => SORT_ASC,
				'model' => SORT_ASC,
				'yend' => SORT_DESC,
				'ystart' => SORT_DESC,
			]);
	}

	/**
	 * @inheritdoc
	 * @return AutoModificationQuery
	 */
	public function findById($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[modification_slug]]" => $id,
		]);
	}

	/**
	 * @inheritdoc
	 * @return AutoModificationQuery
	 */
	public function findBySlug($slug)
	{
		return $this->findById($slug);
	}

	/**
	 * @inheritdoc
	 * @return AutoModificationQuery
	 */
	public function findByBrandId($id)
	{
		return $this->andWhere([
			'brand_slug' => $id,
		]);
	}

	/**
	 * @inheritdoc
	 * @return AutoModificationQuery
	 */
	public function findByModelId($id)
	{
		return $this->andWhere([
			'model_slug' => $id,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoModification[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoModification|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
