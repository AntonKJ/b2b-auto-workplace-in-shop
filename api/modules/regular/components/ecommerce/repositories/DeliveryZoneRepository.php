<?php

namespace api\modules\regular\components\ecommerce\repositories;

use api\modules\regular\models\DeliveryZone;
use common\models\DeliveryZoneDeliveryCity;
use myexample\ecommerce\DeliveryZoneCollection;
use myexample\ecommerce\DeliveryZoneRepositoryInterface;

class DeliveryZoneRepository implements DeliveryZoneRepositoryInterface
{

	public function getCityZoneIdsByCityId($cityId): array
	{

		if (!is_array($cityId)) {
			$cityId = [$cityId];
		}

		$reader = DeliveryZoneDeliveryCity::find()
			->distinct()
			->select(['delivery_zone_id', 'delivery_city_id'])
			->andWhere([
				'delivery_city_id' => $cityId,
			])
			->asArray();

		$data = [];
		foreach ($reader->each() as $row) {
			$data[(int)$row['delivery_city_id']][] = (int)$row['delivery_zone_id'];
		}

		return $data;
	}

	public function getById($ids): DeliveryZoneCollection
	{

		if (!is_array($ids))
			$ids = [$ids];

		$reader = DeliveryZone::find()
			->byId($ids);

		$data = new DeliveryZoneCollection();

		/** @var DeliveryZone $row */
		foreach ($reader->each() as $row)
			$data->add($row->getEcommerceEntity(), (int)$row->id);

		return $data;
	}


}