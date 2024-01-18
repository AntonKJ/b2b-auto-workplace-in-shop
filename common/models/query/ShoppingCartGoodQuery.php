<?php

namespace common\models\query;

use common\interfaces\GoodInterface;
use common\models\ShoppingCart;

/**
 * This is the ActiveQuery class for [[\common\models\ShoppingCartGood]].
 *
 * @see \common\models\ShoppingCartGood
 */
class ShoppingCartGoodQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param GoodInterface $good
	 * @return $this
	 */
	public function byGood(GoodInterface $good)
	{
		return $this->andWhere([
			'entity_id' => $good->getId(),
			'entity_type' => $good::getGoodEntityType(),
		]);
	}

	/**
	 * @param ShoppingCart $cart
	 * @return $this
	 */
	public function byCart(ShoppingCart $cart)
	{
		return $this->andWhere([
			'cart_id' => $cart->getId(),
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShoppingCartGood[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShoppingCartGood|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
