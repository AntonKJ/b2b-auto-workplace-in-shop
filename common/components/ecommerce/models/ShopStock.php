<?php

namespace common\components\ecommerce\models;

use myexample\ecommerce\GoodIdentityInterface;
use myexample\ecommerce\ShopStockModelInterface;

class ShopStock implements ShopStockModelInterface
{

	protected $_shopId;
	protected $_amount;
	protected $_goodIdentity;

	public function __construct(int $shopId, int $amount, GoodIdentityInterface $goodIdentity)
	{
		$this->_shopId = $shopId;
		$this->_amount = $amount;
		$this->_goodIdentity = $goodIdentity;
	}

	/**
	 * @return int
	 */
	public function getShopId(): int
	{
		return $this->_shopId;
	}

	/**
	 * @return int
	 */
	public function getAmount(): int
	{
		return $this->_amount;
	}

	public function getGoodIdentity(): GoodIdentityInterface
	{
		return $this->_goodIdentity;
	}

}
