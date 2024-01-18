<?php

namespace common\components\ecommerce\models;

use myexample\ecommerce\ShopGroupMoveModelInterface;

class ShopGroupMove implements ShopGroupMoveModelInterface
{

	protected $_id;
	protected $_groupIdFrom;
	protected $_groupIdTo;
	protected $_days;
	protected $_priority;

	public function __construct(int $id, int $groupIdFrom, int $groupIdTo, int $days, int $priority)
	{
		$this->_id = $id;
		$this->_groupIdFrom = $groupIdFrom;
		$this->_groupIdTo = $groupIdTo;
		$this->_days = $days;
		$this->_priority = $priority;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_id;
	}

	/**
	 * @return int
	 */
	public function getGroupIdFrom(): int
	{
		return $this->_groupIdFrom;
	}

	/**
	 * @return int
	 */
	public function getGroupIdTo(): int
	{
		return $this->_groupIdTo;
	}

	/**
	 * @return int
	 */
	public function getDays(): int
	{
		return $this->_days;
	}

	public function getPriority(): int
	{
		return $this->_priority;
	}

}
