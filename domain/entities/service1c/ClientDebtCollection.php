<?php

namespace domain\entities\service1c;

use domain\collections\EntityCollectionBase;

class ClientDebtCollection extends EntityCollectionBase implements ClientDebtCollectionInterface
{

	final public function add(ClientDebt $data, $key = null)
	{
		$this->_add($data, $key);
	}

}