<?php

namespace domain\entities\car;

use domain\collections\EntityCollectionBase;

class ModificationCollection extends EntityCollectionBase implements ModificationEntityCollectionInterface
{

	final public function add(Modification $data, $key = null)
	{
		$this->_add($data, $key);
	}

}