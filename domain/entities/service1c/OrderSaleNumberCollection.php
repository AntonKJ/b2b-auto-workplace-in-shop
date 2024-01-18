<?php

namespace domain\entities\service1c;

use domain\collections\EntityCollectionBase;

class OrderSaleNumberCollection extends EntityCollectionBase implements OrderSaleNumberCollectionInterface
{

	final public function add(OrderSaleNumber $data, $key = null)
	{
		$this->_add($data, $key);
	}

}