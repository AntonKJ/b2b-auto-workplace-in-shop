<?php

namespace domain\entities\service1c;

use domain\collections\EntityCollectionBase;

class SearchOrderCollection extends EntityCollectionBase implements SearchOrderEntityCollectionInterface
{

	final public function add(SearchOrder $data, $key = null)
	{
		$this->_add($data, $key);
	}

}