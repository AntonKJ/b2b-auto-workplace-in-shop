<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\ShopStock]].
 *
 * @see \common\models\ShopStock
 */
class ShopStockQuery extends \yii\db\ActiveQuery
{

	public function byShopId($id)
	{

		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[shop_id]]" => $id]);
	}

	/**
	 * @param $id
	 * @return ShopStockQuery
	 * @deprecated use byGoodId($id) instead
	 */
	public function findByGoodId($id)
	{

		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[item_idx]]" => $id]);
	}

	public function byGoodId($id)
	{

		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[item_idx]]" => $id]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShopStock[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShopStock|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
