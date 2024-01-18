<?php

namespace domain\entities\service1c;

use domain\interfaces\EntityCollectionInterface;

interface OrderGoodEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(OrderGood $data, $key = null);
}