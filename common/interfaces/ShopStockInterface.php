<?php

namespace common\interfaces;

interface ShopStockInterface
{

	public function getGoodId(): string;

	public function getShopId(): int;

	public function getAmount(): int;

}