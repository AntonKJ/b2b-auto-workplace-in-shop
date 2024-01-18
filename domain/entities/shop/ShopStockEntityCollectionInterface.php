<?php

namespace domain\entities\shop;

use domain\interfaces\EntityCollectionInterface;

interface ShopStockEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(ShopStock $data, $key = null);
}