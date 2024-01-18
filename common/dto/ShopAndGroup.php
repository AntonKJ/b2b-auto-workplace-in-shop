<?php

namespace common\dto;

/**
 * Class ShopAndGroup
 * @package common\dto
 */
class ShopAndGroup implements IDTO
{

	public $shopId;
	public $groupId;
	public $zoneId;
	public $title;

	public function __construct(int $shopId, int $groupId, int $zoneId, string $title)
	{

		$this->shopId = $shopId;
		$this->groupId = $groupId;
		$this->zoneId = $zoneId;
		$this->title = $title;

	}

}