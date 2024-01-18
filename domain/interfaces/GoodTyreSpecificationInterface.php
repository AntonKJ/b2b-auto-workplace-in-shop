<?php

namespace domain\interfaces;

use domain\entities\PriceRange;

interface GoodTyreSpecificationInterface extends SpecificationInterface
{

	public function getId();

	public function getRunflat();

	public function getPins();

	public function getSeason();

	public function getSpeedRating();

	public function getLoadIndex();

	public function getPrice(): ?PriceRange;

	public function getBrandUrl();

	public function getModelUrl();

	public function getSale();

}