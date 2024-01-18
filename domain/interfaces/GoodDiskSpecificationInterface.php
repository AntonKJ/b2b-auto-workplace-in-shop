<?php

namespace domain\interfaces;

use domain\entities\PriceRange;

interface GoodDiskSpecificationInterface extends SpecificationInterface
{

	public function getById();

	public function getSale();

	public function getPrice(): ?PriceRange;

}