<?php

namespace domain\entities\car;

use domain\entities\EntityBase;

/**
 * Class Car
 * @package core\entities
 */
class Car extends EntityBase
{

	private $id;

	protected $brand;
	protected $model;
	protected $modification;


	public function __construct(int $id, Brand $brand, Model $model, Modification $modification)
	{
		$this->id = $id;

		$this->brand = $brand;
		$this->model = $model;
		$this->modification = $modification;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return Brand
	 */
	public function getBrand(): Brand
	{
		return $this->brand;
	}

	/**
	 * @return Model
	 */
	public function getModel(): Model
	{
		return $this->model;
	}

	/**
	 * @return Modification
	 */
	public function getModification(): Modification
	{
		return $this->modification;
	}

}