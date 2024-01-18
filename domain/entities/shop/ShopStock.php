<?php

namespace domain\entities\shop;

use domain\entities\EntityBase;

/**
 * Class ShopStock
 * @package core\entities\shop
 */
class ShopStock extends EntityBase
{

	protected $shop_id;
	protected $good_id;
	protected $amount;

	private $id;

	public function __construct(int $id, int $shop, int $good, int $amount)
	{
		$this->id = $id;

		$this->shop_id = $shop;
		$this->good_id = $good;

		$this->amount = $amount;
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
	public function getGoodId(): int
	{
		return $this->good_id;
	}

	/**
	 * @return int
	 */
	public function getAmount(): int
	{
		return $this->amount;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

}