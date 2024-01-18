<?php


namespace api\modules\regular\models;

use common\components\ecommerce\models\DeliveryCity as DeliveryCityEntity;
use common\models\query\OrderTypeQuery;
use myexample\ecommerce\GeoPosition;

/**
 * @property DeliveryCityEntity $ecommerceEntity
 */
class DeliveryCity extends \common\models\DeliveryCity
{

	/**
	 * @var DeliveryCityEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): DeliveryCityEntity
	{

		if ($this->_ecommerceEntity === null) {

			$geoPosition = new GeoPosition(...$this->getGeoPosition());

			$this->_ecommerceEntity = new DeliveryCityEntity(
				(int)$this->id,
				$this->getTitle(),
				$geoPosition,
				$this->getDeliveryDaysMask(),
				$this->distance
			);
		}

		return $this->_ecommerceEntity;
	}

	/**
	 * @return OrderTypeQuery|\yii\db\ActiveQuery
	 */
	public function getOrderTypes()
	{
		return $this
			->hasMany(OrderType::class, ['ot_id' => 'order_type_id'])
			->via('zones')
			->inverseOf('cities');
	}

}
