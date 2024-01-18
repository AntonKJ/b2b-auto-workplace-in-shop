<?php

namespace common\models\query;

use common\models\MetroLine;

/**
 * This is the ActiveQuery class for [[MetroLine]].
 *
 * @see MetroLine
 */
class MetroLineQuery extends \yii\db\ActiveQuery
{

	public function defaultOrder()
	{
		return $this->addOrderBy([
			'sortorder' => SORT_ASC,
			'title' => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return MetroLine[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return MetroLine|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
