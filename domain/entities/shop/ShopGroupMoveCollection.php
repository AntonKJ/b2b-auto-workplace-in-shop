<?php

namespace domain\entities\shop;

use domain\collections\EntityCollectionBase;

class ShopGroupMoveCollection extends EntityCollectionBase implements ShopGroupMoveEntityCollectionInterface
{

	final public function add(ShopGroupMove $data, $key = null)
	{
		$this->_add($data, $key);
	}

}