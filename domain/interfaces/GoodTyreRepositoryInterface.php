<?php

namespace domain\interfaces;

use domain\entities\good\BrandTyre;
use domain\entities\good\GoodTyre;
use domain\entities\good\ModelTyre;
use domain\entities\region\RegionEntityCollectionInterface;

interface GoodTyreRepositoryInterface extends GoodRepositoryInterface
{

	public function findAllBySpecification(GoodTyreSpecificationInterface $specification): RegionEntityCollectionInterface;

	public function findOneBySpecification(GoodTyreSpecificationInterface $specification): GoodTyre;

	public function getBrandByCode($code): BrandTyre;

	public function getModelByBrandAndCode(BrandTyre $brand, $code): ModelTyre;

	public function loadBrandInto(GoodEntityInterface &$good, BrandTyre $brand): GoodEntityInterface;

	public function loadModelInto(GoodEntityInterface &$good, ModelTyre $model): GoodEntityInterface;

}