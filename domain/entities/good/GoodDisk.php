<?php

namespace domain\entities\good;

use domain\entities\EntityBase;
use domain\entities\SizeDisk;
use domain\interfaces\GoodEntityInterface;

class GoodDisk extends EntityBase implements GoodEntityInterface
{

	const ENTITY_TYPE = 'disk';

	private $id;

	protected $sku;
	protected $title;
	//
	protected $brandSku;
	protected $brandId;
	//
	protected $modelId;
	//
	protected $size;
	protected $params;

	protected $brand;
	protected $model;

	/**
	 * DiskGood constructor.
	 * @param $id
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}

	public function getEntityType()
	{
		return static::ENTITY_TYPE;
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getSku()
	{
		return $this->sku;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getBrandSku()
	{
		return $this->brandSku;
	}

	/**
	 * @return mixed
	 */
	public function getBrandId()
	{
		return $this->brandId;
	}

	/**
	 * @return mixed
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * @return SizeDisk
	 */
	public function getSize(): SizeDisk
	{
		return $this->size;
	}

	/**
	 * @return GoodDiskParams
	 */
	public function getParams(): GoodDiskParams
	{
		return $this->params;
	}

	public function getBrand()
	{
		return $this->brand;
	}

	public function getModel()
	{
		return $this->model;
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{

		$fields = [

			'id' => $this->getId(),

			'type' => $this->getEntityType(),

			'sku' => $this->getSku(),

			'title' => $this->getTitle(),

			'brandSku' => $this->getBrandSku(),
			'brandId' => $this->getBrandId(),

			'modelId' => $this->getModelId(),

			'size' => $this->getSize(),
			'sizeText' => $this->getSize()->format(),

			'params' => $this->getParams(),

			'brand' => $this->getBrand(),
			'model' => $this->getModel(),

		];

		return $fields;
	}

}