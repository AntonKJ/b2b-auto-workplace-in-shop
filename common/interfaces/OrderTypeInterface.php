<?php

namespace common\interfaces;

interface OrderTypeInterface
{

	public function getFromShopId();

	public function getDays(): int;

	public function getCategory(): string;

	public function getDeliverySchedule(): array;

}