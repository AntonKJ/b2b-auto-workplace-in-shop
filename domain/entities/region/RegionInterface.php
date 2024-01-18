<?php

namespace domain\entities\region;

use domain\entities\EntityBase;
use domain\entities\GeoPosition;
use domain\traits\DeliveryDaysTrait;

/**
 * Class Region
 * @package core\entities\region
 */
class RegionInterface extends EntityBase implements RegionEntityInterface
{

	use DeliveryDaysTrait;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	protected $group_id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $slug;

	/**
	 * @var int
	 */
	protected $zone_id;

	/**
	 * @var string
	 */
	protected $zone_type;

	/**
	 * @var int
	 */
	protected $alt_zone_id;

	/**
	 * @var int
	 */
	protected $shops_from_region_id;

	/**
	 * @var bool
	 */
	protected $is_reg_movement;

	/**
	 * @var string
	 */
	protected $delivery_notes;

	/**
	 * @var int
	 */
	protected $delivery_days;

	/**
	 * @var int
	 */
	protected $delivery_type;

	/**
	 * @var int|null
	 */
	protected $order_type_group_id;

	/**
	 * @var GeoPosition|null
	 */
	protected $geo_position;

	/**
	 * Region constructor.
	 * @param int $id
	 * @param int $group_id
	 * @param string $title
	 * @param string $slug
	 * @param int $zone_id
	 * @param int $alt_zone_id
	 * @param int $shops_from_region_id
	 * @param bool $is_reg_movement
	 * @param int $delivery_type
	 * @param string $delivery_notes
	 * @param int $delivery_days
	 * @param int $order_type_group_id
	 * @param GeoPosition $geoPosition
	 */
	public function __construct(int $id, $group_id, $title, $slug, $zone_id, $zone_type, $alt_zone_id, $shops_from_region_id, $is_reg_movement, $delivery_type, $delivery_notes, $delivery_days, $order_type_group_id, GeoPosition $geoPosition)
	{

		$this->id = $id;
		$this->group_id = $group_id;
		$this->title = $title;
		$this->slug = $slug;
		$this->zone_id = $zone_id;
		$this->zone_type = $zone_type;
		$this->alt_zone_id = $alt_zone_id;
		$this->shops_from_region_id = $shops_from_region_id;
		$this->is_reg_movement = $is_reg_movement;

		$this->delivery_type = $delivery_type;
		$this->delivery_notes = $delivery_notes;
		$this->delivery_days = $delivery_days;
		$this->order_type_group_id = $order_type_group_id;
		$this->geo_position = $geoPosition;
	}

	/**
	 * @return int
	 */
	public function getDeliveryDaysMask(): int
	{
		return (int)$this->delivery_days;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getGroupId(): int
	{
		return $this->group_id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getSlug(): string
	{
		return $this->slug;
	}

	/**
	 * @return int
	 */
	public function getZoneId(): int
	{
		return $this->zone_id;
	}

	/**
	 * @return int
	 */
	public function getAltZoneId(): int
	{
		return $this->alt_zone_id;
	}

	/**
	 * Возвращает актуальное zone_id для региона с учетом альтернативной зоны
	 * @return int
	 */
	public function getPriceZoneId(): int
	{
		return ($zId = (int)$this->alt_zone_id) > 0 ? $zId : $this->zone_id;
	}

	/**
	 * Возвращает ID региона для подгрузки магазинов
	 * @return int|null
	 */
	public function getRegionIdForShops()
	{
		return ($id = (int)$this->shops_from_region_id) > 0 ? $id : $this->id;
	}

	/**
	 * Есть ли перемещение товара из других регионов в этот
	 * @return bool
	 */
	public function isMovementToRegion(): bool
	{
		return $this->is_reg_movement;
	}

	/**
	 * @return int|null
	 */
	public function getDeliveryTypeId(): ?int
	{
		return $this->delivery_type;
	}

	public function getZoneType()
	{
		return $this->zone_type;
	}

	/**
	 * @inheritdoc
	 */
	public function getDeliveryNotes(): ?string
	{
		return $this->delivery_notes;
	}

	public function fields()
	{

		/*
		Array
			(
			[group_id] => group_id
			[title] => title
			[slug] => slug
			[zone_id] => zone_id
			[zone_type] => zone_type
			[alt_zone_id] => alt_zone_id
			[shops_from_region_id] => shops_from_region_id
			[is_reg_movement] => is_reg_movement
			[delivery_notes] => delivery_notes
			[delivery_days] => delivery_days
			[delivery_type] => delivery_type
		)
		*/

		$geoPosition = null;
		if (($pos = $this->getGeoPosition()) instanceof GeoPosition)
			$geoPosition = [$pos->getLat(), $pos->getLng()];

		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'delivery' => [
				'days' => $this->getDeliveryDays(),
				'notes' => $this->getDeliveryNotes(),
			],
			'geoPosition' => $geoPosition,
		];
	}

	public function getOrderTypeGroupId()
	{
		return $this->order_type_group_id;
	}

	public function getGeoPosition(): ?GeoPosition
	{
		return $this->geo_position;
	}

}