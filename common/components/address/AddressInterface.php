<?php

namespace common\components\address;

use domain\entities\GeoPosition;

interface AddressInterface
{

	public function getId(): int;

	public function getAddress(): string;

	public function getGeoPosition(): GeoPosition;

}