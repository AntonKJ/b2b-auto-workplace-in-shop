<?php

namespace domain\interfaces;

interface RegionSpecificationInterface extends SpecificationInterface
{

	const ORDER_DEFAULT = 1;

	public function getId();

	public function getSlug();

	public function getDeliveryType();

	public function getZoneId();

	public function isActive();

	public function getOrderBy();
}