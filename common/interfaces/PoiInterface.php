<?php

namespace common\interfaces;

use common\models\OrderType;
use common\models\query\OrderTypeQuery;
use domain\entities\GeoPosition;

/**
 * @property string $poiType
 * @property string $geoPosition
 */
interface PoiInterface
{

	/**
	 * @return OrderTypeQuery
	 */
	public function getOrderTypeQuery();

	/**
	 * @return string
	 */
	public function getPoiType();

	/**
	 * @return GeoPosition|null|float[]|array
	 */
	public function getGeoPosition();

	/**
	 * @return null|float
	 */
	public function getDistance();

	/**
	 * Возвращает радиус области или null если область не нужна
	 * @return null|float
	 */
	public function getAreaRadius();

}