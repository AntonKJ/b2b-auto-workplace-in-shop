<?php

namespace common\models\query;

use common\models\CacheVariables;

/**
 * This is the ActiveQuery class for [[CacheVariables]].
 *
 * @see CacheVariables
 */
class CacheVariablesQuery extends \yii\db\ActiveQuery
{

	public function byId($id)
	{
		return $this->andWhere([
			'[[id]]' => $id,
		]);
	}

	/**
	 * @inheritdoc
	 * @return CacheVariables[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return CacheVariables|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
