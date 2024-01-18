<?php

namespace domain\entities\service1c;

use domain\collections\EntityCollectionBase;

class OrderCollection extends EntityCollectionBase implements OrderEntityCollectionInterface
{

	final public function add(Order $data, $key = null)
	{
		$this->_add($data, $key);
	}

}