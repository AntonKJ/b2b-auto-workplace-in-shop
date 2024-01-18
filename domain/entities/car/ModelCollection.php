<?php

namespace domain\entities\car;

use domain\collections\EntityCollectionBase;

class ModelCollection extends EntityCollectionBase implements ModelEntityCollectionInterface
{

	final public function add(Model $data, $key = null)
	{
		$this->_add($data, $key);
	}

}