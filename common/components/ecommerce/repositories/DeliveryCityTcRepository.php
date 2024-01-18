<?php

namespace common\components\ecommerce\repositories;

use common\components\ecommerce\models\DeliveryCityTc;
use common\models\Region;
use myexample\ecommerce\DeliveryCityTcCollection;
use myexample\ecommerce\DeliveryCityTcRepositoryInterface;
use myexample\ecommerce\DeliveryTcOptionsTrait;
use myexample\ecommerce\RegionEntityInterface;
use Yii;

class DeliveryCityTcRepository implements DeliveryCityTcRepositoryInterface
{

	use DeliveryTcOptionsTrait;

	/**
	 * @param RegionEntityInterface $region
	 * @return DeliveryCityTcCollection
	 */
	public function getDeliveryCityOrderedByDefault(RegionEntityInterface $region): DeliveryCityTcCollection
	{
		static $deliveryCities = [];
		if (!isset($deliveryCities[$region->getZoneId()])) {
			$key = __METHOD__ . '_' . $region->getZoneId();
			$deliveryCities[$region->getZoneId()] = Yii::$app->getCache()->getOrSet($key, static function () use ($region) {
				$deliveryCities = new DeliveryCityTcCollection();
				$reader = Region::find()
					->byDeliveryTypeId(3)// фильтруем по типу 3 - ПЭК
					->byZoneId($region->getZoneId())
					->defaultOrder();
				/** @var Region $row */
				foreach ($reader->each() as $row) {
					$city = new DeliveryCityTc($row->getId(), $row->getTitle());
					$deliveryCities->add($city, $city->getId());
				}
				return $deliveryCities;
			});
		}
		return $deliveryCities[$region->getZoneId()];
	}

}
