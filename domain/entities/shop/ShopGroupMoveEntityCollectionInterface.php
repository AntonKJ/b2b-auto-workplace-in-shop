<?php

namespace domain\entities\shop;

use domain\interfaces\EntityCollectionInterface;

interface ShopGroupMoveEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(ShopGroupMove $data, $key = null);
}