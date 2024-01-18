<?php

namespace domain\entities\good;

use domain\entities\EntityBase;
use domain\entities\SizeTyre;
use domain\interfaces\GoodEntityInterface;

class GoodTyre extends EntityBase implements GoodEntityInterface
{

	const ENTITY_TYPE = 'tyre';

	private $id;

	protected $sku;
	protected $sku_1c;

	protected $title;

	protected $brandSku;
	protected $brandCode;

	protected $country;

	protected $modelCode;

	protected $size;
	protected $params;

	protected $brand;
	protected $model;

	/**
	 * GoodTyre constructor.
	 * @param $id
	 * @param $sku
	 * @param $sku_1c
	 * @param $title
	 * @param $brandSku
	 * @param $brandCode
	 * @param $country
	 * @param $modelCode
	 * @param SizeTyre $size
	 * @param GoodTyreParams $params
	 */
	public function __construct($id, $sku, $sku_1c, $title, $brandSku, $brandCode, $country, $modelCode, SizeTyre $size, GoodTyreParams $params)
	{
		$this->id = $id;
		$this->sku = $sku;
		$this->sku_1c = $sku_1c;
		$this->title = $title;
		$this->brandSku = $brandSku;
		$this->brandCode = $brandCode;
		$this->country = $country;
		$this->modelCode = $modelCode;
		$this->size = $size;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
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
	public function getSku1c()
	{
		return $this->sku_1c;
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
	public function getBrandCode()
	{
		return $this->brandCode;
	}

	/**
	 * @return mixed
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @return mixed
	 */
	public function getModelCode()
	{
		return $this->modelCode;
	}

	/**
	 * @return SizeTyre
	 */
	public function getSize(): SizeTyre
	{
		return $this->size;
	}

	/**
	 * @return GoodTyreParams
	 */
	public function getParams(): GoodTyreParams
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
			'sku1c' => $this->getSku1c(),

			'title' => $this->getTitle(),

			'brandSku' => $this->getBrandSku(),
			'brandCode' => $this->getBrandCode(),
			'country' => $this->getCountry(),

			'modelCode' => $this->getModelCode(),

			'size' => $this->getSize(),
			'sizeText' => $this->getSize()->format(),

			'params' => $this->getParams(),

			'brand' => $this->getBrand(),
			'model' => $this->getModel(),

		];

		return $fields;
	}

}