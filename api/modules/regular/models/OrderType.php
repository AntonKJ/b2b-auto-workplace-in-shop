<?php


namespace api\modules\regular\models;

use common\components\ecommerce\models\OrderType as OrderTypeEntity;

/**
 * @property OrderTypeEntity $ecommerceEntity
 */
class OrderType extends \common\models\OrderType
{

	/**
	 * @var OrderTypeEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): OrderTypeEntity
	{

		if ($this->_ecommerceEntity === null)
			$this->_ecommerceEntity = new OrderTypeEntity(
				$this->getId(),
				$this->getCategory(),
				$this->getFromShopId(),
				$this->getTitle(),
				$this->getDays(),
				(int)$this->delivery_schedule_id,
				(int)$this->allowed_paytypes,
				(int)$this->nextday_time
			);

		return $this->_ecommerceEntity;
	}

}
