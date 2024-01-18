<?php

namespace domain\entities\service1c;

use domain\interfaces\EntityCollectionInterface;

interface OrderEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(Order $data, $key = null);
}