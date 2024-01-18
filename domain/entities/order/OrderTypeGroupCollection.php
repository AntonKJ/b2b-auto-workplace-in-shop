<?php

namespace domain\entities\order;

use domain\collections\EntityCollectionBase;

class OrderTypeGroupCollection extends EntityCollectionBase implements OrderTypeGroupEntityCollectionInterface
{

	final public function add(OrderTypeGroup $data, $key = null)
	{
		$this->_add($data, $key);
	}

}