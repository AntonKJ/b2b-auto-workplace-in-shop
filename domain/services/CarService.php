<?php

namespace domain\services;

use domain\entities\car\Brand;
use domain\entities\car\BrandEntityCollectionInterface;
use domain\entities\car\Model;
use domain\entities\car\ModelEntityCollectionInterface;
use domain\entities\car\ModificationEntityCollectionInterface;
use domain\interfaces\CarRepositoryInterface;
use domain\interfaces\CarServiceInterface;

class CarService implements CarServiceInterface
{
	protected $carRepository;

	public function __construct(CarRepositoryInterface $carRepository)
	{
		$this->carRepository = $carRepository;
	}

	/**
	 * @return array
	 */
	public function getBrands(): BrandEntityCollectionInterface
	{
		return $this->carRepository->findBrandAll();
	}

	public function getBrandBySlug(string $slug): Brand
	{
		return $this->carRepository->findBrandBySlug($slug);
	}

	public function getModelsByBrandId($id): ModelEntityCollectionInterface
	{
		return $this->carRepository->findModelAllByBrandId($id);
	}

	public function getModelByBrandIdAndSlug($brandId, string $slug): Model
	{
		return $this->carRepository->findModelByBrandIdAndSlug($brandId, $slug);
	}

	public function getModificationsByBrandIdAndModelId($brandId, $modelId): ModificationEntityCollectionInterface
	{
		return $this->carRepository->findModificationAllByBrandIdAndModelId($brandId, $modelId);
	}

	public function search($keyword)
	{

	}

}