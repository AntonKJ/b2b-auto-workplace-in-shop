<?php

namespace domain\entities\region;

use domain\entities\EntityBase;

/**
 * Class RegionCrosses
 * @package core\entities\region
 */
class RegionCrosses extends EntityBase
{

	/**
	 * @var int
	 */
	protected $shop_id;

	/**
	 * @var int
	 */
	protected $region_id;

	/**
	 * ShopCrosses constructor.
	 * @param int $shop_id
	 * @param int $region_id
	 */
	public function __construct(int $shop_id, int $region_id)
	{
		$this->shop_id = $shop_id;
		$this->region_id = $region_id;
	}

	/**
	 * @return int
	 */
	public function getShopId(): int
	{
		return $this->shop_id;
	}

	/**
	 * @return int
	 */
	public function getRegionId(): int
	{
		return $this->region_id;
	}

}