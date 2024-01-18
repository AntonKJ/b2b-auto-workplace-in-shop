<?php

namespace domain\entities\car;

use domain\collections\EntityCollectionBase;

class BrandCollection extends EntityCollectionBase implements BrandEntityCollectionInterface
{

	final public function add(Brand $data, $key = null)
	{
		$this->_add($data, $key);
	}

}