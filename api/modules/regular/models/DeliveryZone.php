<?php

namespace api\modules\regular\models;

use common\components\ecommerce\models\DeliveryZone as DeliveryZoneEntity;

/**
 * @property DeliveryZone $ecommerceEntity
 */
class DeliveryZone extends \common\models\DeliveryZone
{

	/**
	 * @var DeliveryZone
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): DeliveryZoneEntity
	{

		if ($this->_ecommerceEntity === null)
			$this->_ecommerceEntity = new DeliveryZoneEntity(
				(int)$this->id,
				(int)$this->order_type_id,
				$this->getDeliveryAreaArray()
			);

		return $this->_ecommerceEntity;
	}

}
