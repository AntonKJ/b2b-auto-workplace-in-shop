<?php

namespace common\components\ecommerce\models;

use myexample\ecommerce\ShopModelInterface;

class Shop implements ShopModelInterface
{

	protected $_id;
	protected $_groupId;
	protected $_zoneId;
	protected $_title;
	protected $_isStorageOnly;
	protected $_isStorageSupplier;

	public function __construct(int $id, int $groupId, int $zoneId, string $title, bool $isStorageOnly, bool $isStorageSupplier)
	{
		$this->_id = $id;
		$this->_groupId = $groupId;
		$this->_zoneId = $zoneId;
		$this->_title = $title;
		$this->_isStorageOnly = $isStorageOnly;
		$this->_isStorageSupplier = $isStorageSupplier;
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
	public function getGroupId(): int
	{
		return $this->_groupId;
	}

	/**
	 * @return int
	 */
	public function getZoneId(): int
	{
		return $this->_zoneId;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->_title;
	}

	/**
	 * @return bool
	 */
	public function getIsStorageOnly(): bool
	{
		return $this->_isStorageOnly;
	}

	/**
	 * @return bool
	 */
	public function getIsStorageSupplier(): bool
	{
		return $this->_isStorageSupplier;
	}

}
