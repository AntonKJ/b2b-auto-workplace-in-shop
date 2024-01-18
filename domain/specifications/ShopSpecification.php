<?php

namespace domain\specifications;

use domain\interfaces\ShopSpecificationInterface;

class ShopSpecification implements ShopSpecificationInterface
{

	/**
	 * @var bool
	 */
	protected $shopIdIsSet;

	/**
	 * @var bool
	 */
	protected $groupIdIsSet;

	/**
	 * @var bool
	 */
	protected $active;

	/**
	 * @var bool
	 */
	protected $notShow;

	/**
	 * @var int
	 */
	protected $regionId;

	/**
	 * @var int
	 */
	protected $crossesFromRegionId;

	/**
	 * @var int
	 */
	protected $orderBy;

	/**
	 * @return bool|null
	 */
	public function isShopIdIsSet()
	{
		return $this->shopIdIsSet;
	}

	/**
	 * @param bool|null $shopIdIsSet
	 * @return ShopSpecification
	 */
	public function setShopIdIsSet($shopIdIsSet = true): ShopSpecification
	{
		$this->shopIdIsSet = $shopIdIsSet;
		return $this;
	}

	/**
	 * @return bool|null
	 */
	public function isGroupIdIsSet()
	{
		return $this->groupIdIsSet;
	}

	/**
	 * @param bool|null $groupIdIsSet
	 * @return ShopSpecification
	 */
	public function setGroupIdIsSet($groupIdIsSet = true): ShopSpecification
	{
		$this->groupIdIsSet = $groupIdIsSet;
		return $this;
	}

	/**
	 * @return bool|null
	 */
	public function isActive()
	{
		return $this->active;
	}

	/**
	 * @param bool|null $active
	 * @return ShopSpecification
	 */
	public function setActive($active = true): ShopSpecification
	{
		$this->active = $active;
		return $this;
	}

	/**
	 * @return bool|null
	 */
	public function isNotShow()
	{
		return $this->notShow;
	}

	/**
	 * @param bool|null $notShow
	 * @return ShopSpecification
	 */
	public function setNotShow($notShow = true): ShopSpecification
	{
		$this->notShow = $notShow;
		return $this;
	}

	/**
	 * @return ShopSpecification
	 */
	public function setPublished(): ShopSpecification
	{
		return $this
			->setActive(true)
			->setNotShow(true)
			->setShopIdIsSet(true);
	}

	/**
	 * @return ShopSpecification
	 */
	public function setOrderDefault(): ShopSpecification
	{
		$this->orderBy = static::ORDER_DEFAULT;
		return $this;
	}

	public function getOrderBy()
	{
		return $this->orderBy;
	}

	/**
	 * @return mixed
	 */
	public function getRegionId()
	{
		return $this->regionId;
	}

	/**
	 * @param int $regionId
	 * @return ShopSpecification
	 */
	public function setRegionId(int $regionId): ShopSpecification
	{
		$this->regionId = $regionId;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getCrossesFromRegionId()
	{
		return $this->crossesFromRegionId;
	}

	/**
	 * @param int $crossesFromRegionId
	 * @return ShopSpecification
	 */
	public function setCrossesFromRegionId(int $crossesFromRegionId): ShopSpecification
	{
		$this->crossesFromRegionId = $crossesFromRegionId;
		return $this;
	}


}