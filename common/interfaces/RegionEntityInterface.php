<?php

namespace common\interfaces;

use domain\entities\GeoPosition;
use domain\interfaces\DeliveryDaysInterface;

/**
 * Interface RegionInterface
 * @package common\interfaces
 *
 * @property string $title
 */
interface RegionEntityInterface extends OrderTypeGroupableInterface, DeliveryDaysInterface, \domain\entities\region\RegionEntityInterface
{

	/**
	 * Возвращает наименование региона
	 * @return string
	 */
	public function getTitle(): string;

	/**
	 * Возвращает идентификатор `id` региона
	 * @return integer
	 */
	public function getId();

	/**
	 * Возвращает zone_id
	 * @return int
	 */
	public function getZoneId();

	/**
	 * Возвращает zone_type
	 * @return string
	 */
	public function getZoneType();

	/**
	 * Альтернативная зона для региона
	 * @return int
	 */
	public function getAltZoneId(): int;

	/**
	 * Возвращает актуальное zone_id для региона с учетом альтернативной зоны
	 * @return int
	 */
	public function getPriceZoneId();

	/**
	 * Есть ли перемещение товара из других регионов в этот
	 * @return bool
	 */
	public function isMovementToRegion(): bool;

	/**
	 * Возвращает ID региона для подгрузки магазинов
	 * @return int|null
	 */
	public function getRegionIdForShops();

	/**
	 * Возвращает ID типа доставки
	 * @return int|null
	 */
	public function getDeliveryTypeId(): ?int;

	/**
	 * Возвращает замечания по доставке
	 * @return string|null
	 */
	public function getDeliveryNotes(): ?string;

	/**
	 * @return GeoPosition|null
	 */
	public function getGeoPosition(): ?GeoPosition;

	/**
	 * @return bool
	 */
	public function getIsRegionInMoscowGroup(): bool;

	/**
	 * @return string|null
	 */
	public function getPhone();

}