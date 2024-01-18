<?php

namespace domain\entities\region;

use domain\collections\EntityCollectionBase;

class RegionCollection extends EntityCollectionBase implements RegionEntityCollectionInterface
{

	final public function add(RegionEntityInterface $data, $key = null)
	{
		$this->_add($data, $key);
	}

}