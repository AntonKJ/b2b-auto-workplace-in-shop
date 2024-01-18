<?php

namespace common\models\query;

use common\models\DiskColor;

/**
 * This is the ActiveQuery class for [[\common\models\DiskColor]].
 *
 * @see \common\models\DiskColor
 */
class DiskColorQuery extends \yii\db\ActiveQuery
{

	public function published()
	{
		return $this->andWhere(['[[status]]' => DiskColor::STATUS_PUBLISHED]);
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
	 * @return \common\models\DiskColor[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\DiskColor|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
