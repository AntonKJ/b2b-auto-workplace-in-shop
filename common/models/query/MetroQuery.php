<?php

namespace common\models\query;

use common\models\Metro;

/**
 * This is the ActiveQuery class for [[Metro]].
 *
 * @see Metro
 */
class MetroQuery extends \yii\db\ActiveQuery
{

	public function defaultOrder()
	{
		return $this->orderBy([
			'[[title]]' => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return Metro[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Metro|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
