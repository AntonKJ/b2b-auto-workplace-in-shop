<?php

namespace common\interfaces;

use domain\entities\GeoPosition;

interface OrderTypePoiQueryInterface
{

	public function byOrderTypeId($id);

	public function byDistanceEqLess(GeoPosition $position, float $distance);

	public function orderByClosest(GeoPosition $position);

}