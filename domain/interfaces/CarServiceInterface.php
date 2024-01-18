<?php

namespace domain\interfaces;

use domain\entities\car\Brand;
use domain\entities\car\BrandEntityCollectionInterface;
use domain\entities\car\Model;
use domain\entities\car\ModelEntityCollectionInterface;
use domain\entities\car\ModificationEntityCollectionInterface;

interface CarServiceInterface extends ServiceInterface
{

	public function getBrands(): BrandEntityCollectionInterface;

	public function getBrandBySlug(string $slug): Brand;

	public function getModelsByBrandId($id): ModelEntityCollectionInterface;

	public function getModelByBrandIdAndSlug($brandId, string $slug): Model;

	public function getModificationsByBrandIdAndModelId($brandId, $modelId): ModificationEntityCollectionInterface;

}