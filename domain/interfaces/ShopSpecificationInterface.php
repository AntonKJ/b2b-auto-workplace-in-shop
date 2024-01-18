<?php

namespace domain\interfaces;

interface ShopSpecificationInterface extends SpecificationInterface
{

	const ORDER_DEFAULT = 1;

	public function isShopIdIsSet();

	public function isGroupIdIsSet();

	public function isActive();

	public function isNotShow();

	public function getOrderBy();

	public function getRegionId();

	public function getCrossesFromRegionId();

}