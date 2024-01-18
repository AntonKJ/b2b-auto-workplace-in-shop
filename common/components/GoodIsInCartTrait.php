<?php

namespace common\components;

use Yii;
use yii\base\InvalidConfigException;

trait GoodIsInCartTrait
{

	/**
	 * @return ShoppingCart|object|null
	 * @throws InvalidConfigException
	 */
	public function getCart()
	{
		return Yii::$app->get('shoppingCart');
	}

	/**
	 * @return bool|int
	 * @throws InvalidConfigException
	 */
	public function getIsInCart()
	{
		return $this->getCart()->isGoodIdInCart($this->getEntityType(), $this->getId());
	}

}
