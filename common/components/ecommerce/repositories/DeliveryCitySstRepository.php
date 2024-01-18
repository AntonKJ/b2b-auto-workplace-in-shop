<?php

namespace common\components\ecommerce\repositories;

use common\models\DeliveryCitySst;
use myexample\ecommerce\DeliveryCitySstCollection;
use myexample\ecommerce\DeliveryCitySstRepositoryInterface;
use Yii;

class DeliveryCitySstRepository implements DeliveryCitySstRepositoryInterface
{

	/**
	 * @param int $zoneId
	 * @return DeliveryCitySstCollection
	 */
	public function getByActiveByZoneIdOrderedByDefault(int $zoneId): DeliveryCitySstCollection
	{
		static $deliveryCities = [];
		if (!isset($deliveryCities[$zoneId])) {

			$key = __METHOD__ . '_' . $zoneId;
			$deliveryCities[$zoneId] = Yii::$app->getCache()->getOrSet($key, static function () use ($zoneId) {

				$deliveryCities = new DeliveryCitySstCollection();

				$reader = DeliveryCitySst::find()
					->byIsActive()
					->byZoneId($zoneId)
					->defaultOrder();

				/** @var DeliveryCitySst $deliveryCitySst */
				foreach ($reader as $deliveryCitySst) {
					$deliveryCities->add($deliveryCitySst->getEcommerceEntity());
				}

				return $deliveryCities;
			});
		}

		return $deliveryCities[$zoneId];
	}

}
