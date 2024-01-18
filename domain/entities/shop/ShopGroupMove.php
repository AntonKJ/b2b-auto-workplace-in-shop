<?php

namespace domain\entities\shop;

use domain\entities\EntityBase;

/**
 * Class ShopGroupMoves
 * @package core\entities\shop
 */
class ShopGroupMove extends EntityBase
{

	protected $group_id_from;
	protected $group_id_to;

	protected $days;
	protected $priority;

	private $id;

	public function __construct(int $id, int $group_id_from, int $group_id_to, int $days, int $priority)
	{
		$this->id = $id;

		$this->group_id_from = $group_id_from;
		$this->group_id_to = $group_id_to;

		$this->days = $days;
		$this->priority = $priority;
	}

	/**
	 * @return int
	 */
	public function getGroupIdFrom(): int
	{
		return $this->group_id_from;
	}

	/**
	 * @return int
	 */
	public function getGroupIdTo(): int
	{
		return $this->group_id_to;
	}

	/**
	 * @return int
	 */
	public function getDays(): int
	{
		return $this->days;
	}

	/**
	 * @return int
	 */
	public function getPriority(): int
	{
		return $this->priority;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

}
