<?php

namespace domain\entities\shoppingCart\collections;

use domain\entities\shoppingCart\ShoppingCartGood;
use domain\interfaces\EntityCollectionInterface;

interface ShoppingCartGoodCollectionInterface extends EntityCollectionInterface
{
	public function add(ShoppingCartGood $data, $key = null);
}