<?php

namespace domain\entities\shop;

use domain\collections\EntityCollectionBase;

class ShopStockCollection extends EntityCollectionBase implements ShopStockEntityCollectionInterface
{

	final public function add(ShopStock $data, $key = null)
	{
		$this->_add($data, $key);
	}

}