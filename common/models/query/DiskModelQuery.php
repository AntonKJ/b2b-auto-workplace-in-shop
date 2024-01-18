<?php

namespace common\models\query;

use common\models\DiskModel;

/**
 * This is the ActiveQuery class for [[\common\models\DiskModel]].
 *
 * @see \common\models\DiskModel
 */
class DiskModelQuery extends \yii\db\ActiveQuery
{

	public function published()
	{

		$alias = $this->getAlias();

		return $this->andWhere(["{$alias}.[[status]]" => DiskModel::STATUS_PUBLISHED]);
	}

	public function bySlug($slug)
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[slug]]" => $slug,
		]);
	}

	public function byId($ids)
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[id]]" => $ids,
		]);
	}

	/**
	 * @return $this
	 */
	public function defaultOrder()
	{

		$alias = $this->getAlias();

		return $this->orderBy([
			"{$alias}.[[sortorder]]" => SORT_ASC,
			"{$alias}.[[title]]" => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskModel[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskModel|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
