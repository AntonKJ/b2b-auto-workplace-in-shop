<?php

namespace domain\entities\service1c;

use domain\collections\EntityCollectionBase;

class OrderGoodCollection extends EntityCollectionBase implements OrderGoodEntityCollectionInterface
{

	final public function add(OrderGood $data, $key = null)
	{
		$this->_add($data, $key);
	}

}