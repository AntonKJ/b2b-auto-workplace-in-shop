<?php

namespace domain\entities\shop;

use domain\entities\EntityBase;

/**
 * Class ShopNetwork
 * @package core\entities\shop
 */
class ShopNetwork extends EntityBase
{
	/**
	 * @var int $id
	 */
	private $id;

	/**
	 * @var string $title
	 */
	protected $title;

	/**
	 * @var string $title
	 */
	protected $description;

	/**
	 * @var string $color
	 */
	protected $color;

	/**
	 * @var string $class
	 */
	protected $class;

	/**
	 * ShopNetwork constructor.
	 * @param int $id
	 * @param string $title
	 * @param string $description
	 * @param string $color
	 * @param string $class
	 */
	public function __construct(int $id, $title, $description, $color, $class)
	{
		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
		$this->color = $color;
		$this->class = $class;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getColor(): string
	{
		return $this->color;
	}

	/**
	 * @return string
	 */
	public function getClass(): string
	{
		return $this->class;
	}



	public function fields()
	{
		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'description' => $this->getDescription(),
			'color' => $this->getColor(),
			'class' => $this->getClass(),
		];
	}
}