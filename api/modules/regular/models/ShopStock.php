<?php


namespace api\modules\regular\models;

use api\modules\regular\components\ecommerce\GoodIdentity;
use common\components\ecommerce\models\ShopStock as ShopStockEntity;

/**
 *
 * @property ShopStockEntity $ecommerceEntity
 */
class ShopStock extends \common\models\ShopStock
{

	/**
	 * @var ShopStockEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): ShopStockEntity
	{

		if ($this->_ecommerceEntity === null)
			$this->_ecommerceEntity = new ShopStockEntity(
				(int)$this->shop_id,
				(int)$this->amount,
				new GoodIdentity($this->getGoodId())
			);

		return $this->_ecommerceEntity;
	}

}
