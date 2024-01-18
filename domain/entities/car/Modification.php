<?php

namespace domain\entities\car;

use domain\entities\EntityBase;

/**
 * Class Modification
 * @package core\entities
 */
class Modification extends EntityBase
{

	private $id;

	protected $title;
	protected $slug;

	protected $years;

	public function __construct(int $id, string $title, string $slug, ModificationRange $years)
	{
		$this->id = $id;

		$this->title = $title;
		$this->slug = $slug;

		$this->years = $years;
	}

	/**
	 * @return int
	 */
	public function getId(): string
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
	public function getSlug(): string
	{
		return $this->slug;
	}

	/**
	 * @return int
	 */
	public function getYears(): ModificationRange
	{
		return $this->years;
	}

	public function fields()
	{
		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'slug' => $this->getSlug(),
			'years' => $this->getYears(),
		];
	}

}