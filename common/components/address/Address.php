<?php

namespace common\components\address;

use domain\entities\GeoPosition;
use yii\base\Component;

class Address extends Component implements AddressInterface
{

	protected $id;
	protected $address;
	protected $geoPosition;

	public function __construct(int $id, string $address, GeoPosition $geoPosition, array $config = [])
	{
		parent::__construct($config);

		$this->id = $id;
		$this->address = $address;
		$this->geoPosition = $geoPosition;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getAddress(): string
	{
		return $this->address;
	}

	public function getGeoPosition(): GeoPosition
	{
		return $this->geoPosition;
	}

}