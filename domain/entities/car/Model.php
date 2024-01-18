<?php

namespace domain\entities\car;

use domain\entities\EntityBase;

/**
 * Class Model
 * @package core\entities
 */
class Model extends EntityBase
{

	protected $title;
	protected $slug;
	private $id;

	public function __construct(string $id, string $title, string $slug)
	{
		$this->id = $id;
		$this->title = $title;
		$this->slug = $slug;
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

	public function fields()
	{
		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'slug' => $this->getSlug(),
		];
	}
}