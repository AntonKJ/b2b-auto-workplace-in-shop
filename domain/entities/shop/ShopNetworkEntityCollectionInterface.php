<?php

namespace domain\entities\shop;

use domain\interfaces\EntityCollectionInterface;

interface ShopNetworkEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(ShopNetwork $data, $key = null);
}