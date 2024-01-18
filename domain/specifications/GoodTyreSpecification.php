<?php

namespace domain\specifications;

use domain\entities\PriceRange;
use domain\interfaces\GoodTyreSpecificationInterface;

class GoodTyreSpecification implements GoodTyreSpecificationInterface
{

	/**
	 * @var mixed
	 */
	protected $id;

	/**
	 * @var bool|null
	 */
	protected $runflat;

	/**
	 * @var bool|null
	 */
	protected $pins;

	/**
	 * @var mixed
	 */
	protected $season;

	protected $speedRating;

	protected $loadIndex;

	protected $price;

	protected $brandUrl;

	protected $modelUrl;

	protected $sale;

	/**
	 * @return string|string[]
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return GoodTyreSpecification
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return bool|null
	 */
	public function getRunflat()
	{
		return $this->runflat;
	}

	/**
	 * @param bool|null $runflat
	 * @return GoodTyreSpecification
	 */
	public function setRunflat($runflat)
	{
		$this->runflat = $runflat;
		return $this;
	}

	/**
	 * @return bool|null
	 */
	public function getPins()
	{
		return $this->pins;
	}

	/**
	 * @param bool|null $pins
	 * @return GoodTyreSpecification
	 */
	public function setPins($pins)
	{
		$this->pins = $pins;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSeason()
	{
		return $this->season;
	}

	/**
	 * @param mixed $season
	 * @return GoodTyreSpecification
	 */
	public function setSeason($season)
	{
		$this->season = $season;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSpeedRating()
	{
		return $this->speedRating;
	}

	/**
	 * @param mixed $speedRating
	 */
	public function setSpeedRating($speedRating)
	{
		$this->speedRating = $speedRating;
	}

	/**
	 * @return mixed
	 */
	public function getLoadIndex()
	{
		return $this->loadIndex;
	}

	/**
	 * @param mixed $loadIndex
	 */
	public function setLoadIndex($loadIndex)
	{
		$this->loadIndex = $loadIndex;
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
	 * @return GoodTyreSpecification
	 */
	public function setPrice($priceRange)
	{
		$this->price = $priceRange;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBrandUrl()
	{
		return $this->brandUrl;
	}

	/**
	 * @param mixed $brandUrl
	 * @return GoodTyreSpecification
	 */
	public function setBrandUrl($brandUrl)
	{
		$this->brandUrl = $brandUrl;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getModelUrl()
	{
		return $this->modelUrl;
	}

	/**
	 * @param mixed $modelUrl
	 * @return GoodTyreSpecification
	 */
	public function setModelUrl($modelUrl)
	{
		$this->modelUrl = $modelUrl;
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
	 * @return GoodTyreSpecification
	 */
	public function setSale($sale)
	{
		$this->sale = $sale;
		return $this;
	}


}