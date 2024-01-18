<?php

namespace domain\interfaces;

use domain\entities\good\GoodDisk;
use domain\entities\region\RegionEntityCollectionInterface;

interface GoodDiskRepositoryInterface extends GoodRepositoryInterface
{

	public function findAllBySpecification(GoodDiskSpecificationInterface $specification): RegionEntityCollectionInterface;

	public function findOneBySpecification(GoodDiskSpecificationInterface $specification): GoodDisk;

}