<?php

namespace domain\specifications;

use domain\entities\PriceRange;
use domain\interfaces\GoodDiskSpecificationInterface;

class GoodDiskSpecification implements GoodDiskSpecificationInterface
{

	protected $byId;

	protected $sale;

	protected $price;

	/**
	 * @return mixed
	 */
	public function getById()
	{
		return $this->byId;
	}

	/**
	 * @param mixed $byId
	 * @return GoodDiskSpecification
	 */
	public function setById($byId)
	{
		$this->byId = $byId;
		return $this;
	}

	/**
	 * @return PriceRange|null
	 */
	public function getPrice(): ?PriceRange
	{
		return $this->price;
	}

	/**
	 * @param PriceRange|null $priceRange
	 * @return GoodDiskSpecification
	 */
	public function setPrice($priceRange)
	{
		$this->price = $priceRange;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSale()
	{
		return $this->sale;
	}

	/**
	 * @param mixed $sale
	 * @return GoodDiskSpecification
	 */
	public function setSale($sale)
	{
		$this->sale = $sale;
		return $this;
	}

}