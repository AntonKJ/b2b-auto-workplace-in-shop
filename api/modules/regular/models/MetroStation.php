<?php


namespace api\modules\regular\models;

use common\components\ecommerce\models\MetroStation as MetroStationEntity;
use common\models\query\OrderTypeQuery;
use myexample\ecommerce\GeoPosition;
use yii\db\ActiveQuery;

/**
 * @property MetroStationEntity $ecommerceEntity
 */
class MetroStation extends \common\models\MetroStation
{

	/**
	 * @var MetroStationEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): MetroStationEntity
	{

		if ($this->_ecommerceEntity === null) {

			$geoPosition = new GeoPosition(...$this->getGeoPosition());

			$this->_ecommerceEntity = new MetroStationEntity(
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
	 * @return OrderTypeQuery|ActiveQuery
	 */
	public function getOrderType()
	{
		return $this->hasOne(OrderType::class, ['ot_id' => 'order_type_id'])
			->via('orderTypeRel');
	}

}
