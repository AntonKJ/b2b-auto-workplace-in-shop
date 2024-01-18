<?php

namespace common\models\query;

use common\models\DiskVariation;

/**
 * This is the ActiveQuery class for [[\common\models\DiskVariation]].
 *
 * @see \common\models\DiskVariation
 */
class DiskVariationQuery extends \yii\db\ActiveQuery
{

	public function byId($id)
	{

		$alias = $this->getAlias();

		return $this->andWhere(["{$alias}.[[id]]" => $id]);
	}

	public function bySlug($slug)
	{

		$alias = $this->getAlias();

		return $this->andWhere(["{$alias}.[[slug]]" => $slug]);
	}

	public function published()
	{
		$alias = $this->getAlias();

		return $this->andWhere(["{$alias}.[[status]]" => DiskVariation::STATUS_PUBLISHED]);
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
	 * @return \common\models\DiskVariation[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskVariation|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
