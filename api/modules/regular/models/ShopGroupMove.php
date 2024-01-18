<?php


namespace api\modules\regular\models;

use common\components\ecommerce\models\ShopGroupMove as ShopGroupMoveEntity;
use common\models\ShopGroupMoves;

class ShopGroupMove extends ShopGroupMoves
{

	/**
	 * @var ShopGroupMoveEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): ShopGroupMoveEntity
	{

		if ($this->_ecommerceEntity === null)
			$this->_ecommerceEntity = new ShopGroupMoveEntity(
				(int)$this->move_id,
				(int)$this->shop_group_from,
				(int)$this->shop_group_to,
				(int)$this->move_days,
				(int)$this->move_mins
			);

		return $this->_ecommerceEntity;
	}

}
