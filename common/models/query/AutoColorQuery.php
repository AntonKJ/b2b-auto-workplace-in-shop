<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\AutoColor]].
 *
 * @see \common\models\AutoColor
 */
class AutoColorQuery extends \yii\db\ActiveQuery
{

	/**
	 * @return AutoColorQuery
	 */
	public function defaultOrder()
	{
		return $this->orderBy([
			'colorname' => SORT_ASC,
		]);
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
