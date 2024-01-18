<?php

namespace domain\entities\shoppingCart\collections;

use domain\collections\EntityCollectionBase;
use domain\entities\shoppingCart\ShoppingCartGood;

class ShoppingCartGoodCollection extends EntityCollectionBase implements ShoppingCartGoodCollectionInterface
{

	final public function add(ShoppingCartGood $data, $key = null)
	{
		$this->_add($data, $key);
	}

}