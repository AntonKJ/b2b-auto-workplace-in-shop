<?php

namespace common\components\ecommerce\models;

use myexample\ecommerce\DeliveryDaysTrait;
use myexample\ecommerce\GeoPosition;
use myexample\ecommerce\MetroStationModelInterface;
use myexample\ecommerce\PoiRepositoryInterface;
use Yii;
use yii\base\InvalidConfigException;

class MetroStation implements MetroStationModelInterface
{

	use DeliveryDaysTrait;

	protected $_id;
	protected $_title;
	protected $_geoPosition;
	protected $_distance;
	protected $_deliveryDays;

	public function __construct(int $id, string $title, GeoPosition $geoPosition, int $deliveryDays, ?float $distance = null)
	{
		$this->_id = $id;
		$this->_title = $title;
		$this->_geoPosition = $geoPosition;
		$this->_distance = $distance;
		$this->_deliveryDays = $deliveryDays;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->_title;
	}

	/**
	 * @return GeoPosition
	 */
	public function getGeoPosition(): GeoPosition
	{
		return $this->_geoPosition;
	}

	public static function getPoiType(): string
	{
		return \common\models\MetroStation::POI_TYPE;
	}

	public function getDeliveryDaysMask(): int
	{
		return $this->_deliveryDays;
	}

	/**
	 * @return PoiRepositoryInterface
	 * @throws InvalidConfigException
	 */
	public function getPoiRepository(): PoiRepositoryInterface
	{
		static $repository;
		if ($repository === null) {
			if (!Yii::$app->has('ecommerce')) {
				throw new InvalidConfigException('App component `ecommerce` is not defined');
			}
			$repository = Yii::$app->ecommerce->getMetroRepository();
		}
		return $repository;
	}

	public function getDistance(): ?float
	{
		return $this->_distance;
	}

	public function getAreaRadius(): ?float
	{
		return \common\models\MetroStation::AREA_RADIUS;
	}


}
