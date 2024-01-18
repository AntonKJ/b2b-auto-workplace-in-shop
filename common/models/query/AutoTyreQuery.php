<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\AutoTyre]].
 *
 * @see \common\models\AutoTyre
 */
class AutoTyreQuery extends \yii\db\ActiveQuery
{
	public function defaultOrder()
	{
		return $this->orderBy([
			'[[compatibility]]' => SORT_ASC,
			'[[rad]]' => SORT_ASC,
			'[[place]]' => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoTyre[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoTyre|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
