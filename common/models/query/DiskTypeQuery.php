<?php

namespace common\models\query;

use common\models\DiskType;

/**
 * This is the ActiveQuery class for [[\common\models\DiskType]].
 *
 * @see \common\models\DiskType
 */
class DiskTypeQuery extends \yii\db\ActiveQuery
{

	public function published()
	{
		return $this->andWhere(['[[status]]' => DiskType::STATUS_PUBLISHED]);
	}

	public function defaultOrder()
	{
		return $this->orderBy([
			'sortorder' => SORT_ASC,
			'title' => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskType[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskType|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
