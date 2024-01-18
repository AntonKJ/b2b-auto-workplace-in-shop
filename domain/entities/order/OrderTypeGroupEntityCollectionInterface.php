<?php

namespace domain\entities\order;

use domain\interfaces\EntityCollectionInterface;

interface OrderTypeGroupEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(OrderTypeGroup $data, $key = null);
}