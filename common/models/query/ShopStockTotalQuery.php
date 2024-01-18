<?php

namespace common\models\query;

use common\models\ShopStockTotal;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\ShopStock]].
 *
 * @see \common\models\ShopStock
 */
class ShopStockTotalQuery extends ActiveQuery
{

	/**
	 * @param $id
	 * @return ShopStockTotalQuery
	 */
	public function byShopId($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[shop_id]]" => $id]);
	}

	/**
	 * @param $id
	 * @return ShopStockTotalQuery
	 */
	public function byGoodId($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere(["{$alias}.[[item_idx]]" => $id]);
	}

	/**
	 * @inheritdoc
	 * @return ShopStockTotal[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return ShopStockTotal|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
