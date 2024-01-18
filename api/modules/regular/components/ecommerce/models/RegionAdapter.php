<?php

namespace api\modules\regular\components\ecommerce\models;

use myexample\ecommerce\DeliveryDaysTrait;
use myexample\ecommerce\GeoPosition;
use myexample\ecommerce\RegionEntityInterface;

/**
 * Class Region Adapter for Ecommerce component
 * @package api\modules\regular\components\ecommerce\models
 */
class RegionAdapter implements RegionEntityInterface
{

	use DeliveryDaysTrait;

	/**
	 * @var \common\interfaces\RegionEntityInterface
	 */
	protected $_region;
	protected $_geoPosition;

	public function __construct(\common\interfaces\RegionEntityInterface $region)
	{
		$this->_region = $region;
		$this->_geoPosition = new GeoPosition($region->getGeoPosition()->getLat(), $region->getGeoPosition()->getLng());;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_region->getId();
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->_region->getTitle();
	}

	/**
	 * @return int
	 */
	public function getZoneId(): int
	{
		return $this->_region->getZoneId();
	}

	/**
	 * @return string
	 */
	public function getZoneType(): string
	{
		return $this->_region->getZoneType();
	}

	/**
	 * @return int
	 */
	public function getAltZoneId(): int
	{
		return $this->_region->getAltZoneId();
	}

	/**
	 * @return int
	 */
	public function getPriceZoneId(): int
	{
		return $this->_region->getPriceZoneId();
	}

	/**
	 * @return bool
	 */
	public function isMovementToRegion(): bool
	{
		return $this->_region->isMovementToRegion();
	}

	/**
	 * @return int
	 */
	public function getRegionIdForShops(): int
	{
		return $this->_region->getRegionIdForShops();
	}

	/**
	 * @return int
	 */
	public function getDeliveryTypeId(): int
	{
		return $this->_region->getDeliveryTypeId();
	}

	/**
	 * @return string|null
	 */
	public function getDeliveryNotes(): ?string
	{
		return $this->_region->getDeliveryNotes();
	}

	/**
	 * @return GeoPosition
	 */
	public function getGeoPosition(): GeoPosition
	{
		return $this->_geoPosition;
	}

	/**
	 * @return string|null
	 */
	public function getPhone(): ?string
	{
		return $this->_region->getPhone();
	}

	/**
	 * @return int
	 */
	public function getDeliveryDaysMask(): int
	{
		return $this->_region->getDeliveryDaysMask();
	}

	/**
	 * @return int|null
	 */
	public function getOrderTypeGroupId(): ?int
	{
		return $this->_region->getOrderTypeGroupId();
	}

}
