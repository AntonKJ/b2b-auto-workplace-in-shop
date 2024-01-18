<?php

namespace domain\interfaces;

use domain\entities\region\RegionEntityCollectionInterface;
use domain\entities\region\RegionInterface;

interface RegionRepositoryInterface extends RepositoryInterface
{

	public function findOneBySpecification(RegionSpecificationInterface $specification): RegionInterface;

	public function findAllBySpecification(RegionSpecificationInterface $specification): RegionEntityCollectionInterface;

}