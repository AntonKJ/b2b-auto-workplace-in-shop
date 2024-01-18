<?php

namespace domain\entities\shop;

use domain\collections\EntityCollectionBase;

class ShopNetworkCollection extends EntityCollectionBase implements ShopNetworkEntityCollectionInterface
{

	final public function add(ShopNetwork $data, $key = null)
	{
		$this->_add($data, $key);
	}

}