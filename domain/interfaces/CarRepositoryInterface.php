<?php

namespace domain\interfaces;

use domain\entities\car\Brand;
use domain\entities\car\BrandEntityCollectionInterface;
use domain\entities\car\Model;
use domain\entities\car\ModelEntityCollectionInterface;
use domain\entities\car\ModificationEntityCollectionInterface;

interface CarRepositoryInterface extends RepositoryInterface
{

	public function findBrandAll(): BrandEntityCollectionInterface;

	public function findBrandBySlug(string $slug): Brand;

	public function findModelAllByBrandId($id): ModelEntityCollectionInterface;

	public function findModelByBrandIdAndSlug($brandId, string $slug): Model;

	public function findModificationAllByBrandIdAndModelId($brandId, $modelId): ModificationEntityCollectionInterface;

}