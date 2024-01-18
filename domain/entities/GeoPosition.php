<?php

namespace domain\entities;

/**
 * Class GeoPosition
 * @package core\entities
 */
class GeoPosition extends EntityBase
{

	protected $lat;
	protected $lng;

	public function __construct(float $lat, float $lng)
	{
		$this->lat = $lat;
		$this->lng = $lng;
	}

	public function __toString()
	{
		return "{$this->getLat()}, {$this->getLng()}";
	}

	public function __toArray()
	{
		return [$this->getLat(), $this->getLng()];
	}

	/**
	 * @return float
	 */
	public function getLat(): float
	{
		return $this->lat;
	}

	/**
	 * @return float
	 */
	public function getLng(): float
	{
		return $this->lng;
	}

	public function fields()
	{
		return [
			'lat' => $this->lat,
			'lng' => $this->lng,
		];
	}

}
