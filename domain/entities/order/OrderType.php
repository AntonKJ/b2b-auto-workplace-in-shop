<?php

namespace domain\entities\order;

use domain\entities\EntityBase;
use domain\interfaces\OrderTypeEntityInterface;

class OrderType extends EntityBase implements OrderTypeEntityInterface
{

	private $id;

	protected $title;

	protected $from_shop_id;
	protected $sortorder;
	protected $days;
	protected $category;
	protected $region_area;

	/**
	 * TyreGood constructor.
	 * @param $id
	 * @param $title
	 * @param $from_shop_id
	 * @param $sortorder
	 * @param $days
	 * @param $category
	 * @param $region_area
	 */
	public function __construct(int $id, string $title, int $from_shop_id, int $days, int $sortorder, string $category, $region_area)
	{
		$this->id = $id;
		$this->title = $title;
		$this->from_shop_id = $from_shop_id;
		$this->sortorder = $sortorder;
		$this->days = $days;

		$this->category = $category;
		$this->region_area = $region_area;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return int
	 */
	public function getFromShopId(): int
	{
		return $this->from_shop_id;
	}

	/**
	 * @return int
	 */
	public function getSortorder(): int
	{
		return $this->sortorder;
	}

	/**
	 * @return int
	 */
	public function getDays(): int
	{
		return $this->days;
	}

	/**
	 * @return string
	 */
	public function getCategory(): string
	{
		return $this->category;
	}

	/**
	 * @return mixed
	 */
	public function getRegionArea()
	{
		return $this->region_area;
	}


}