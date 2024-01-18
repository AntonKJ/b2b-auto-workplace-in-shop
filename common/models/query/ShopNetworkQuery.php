<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\ShopNetwork]].
 *
 * @see \common\models\ShopNetwork
 */
class ShopNetworkQuery extends \yii\db\ActiveQuery
{

	/**
	 * @return $this
	 */
	public function defaultOrder()
	{
		return $this->orderBy(['name' => SORT_ASC]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShopNetwork[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShopNetwork|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
