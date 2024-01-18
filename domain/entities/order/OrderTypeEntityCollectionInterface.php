<?php

namespace domain\entities\order;

use domain\interfaces\EntityCollectionInterface;

interface OrderTypeEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(OrderType $data, $key = null);
}