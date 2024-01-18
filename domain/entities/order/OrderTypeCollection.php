<?php

namespace domain\entities\order;

use domain\collections\EntityCollectionBase;

class OrderTypeCollection extends EntityCollectionBase implements OrderTypeEntityCollectionInterface
{

	final public function add(OrderType $data, $key = null)
	{
		$this->_add($data, $key);
	}

}