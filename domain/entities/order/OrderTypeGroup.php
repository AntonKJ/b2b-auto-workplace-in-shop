<?php

namespace domain\entities\order;

use domain\entities\EntityBase;
use domain\interfaces\OrderTypeGroupEntityInterface;

class OrderTypeGroup extends EntityBase implements OrderTypeGroupEntityInterface
{

	private $id;
	protected $title;

	/**
	 * TyreGood constructor.
	 * @param int $id
	 * @param string $title
	 */
	public function __construct(int $id, string $title)
	{
		$this->id = $id;
		$this->title = $title;
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

}