<?php

namespace domain\entities\service1c;

use domain\interfaces\EntityCollectionInterface;

interface OrderSaleNumberCollectionInterface extends EntityCollectionInterface
{
	public function add(OrderSaleNumber $data, $key = null);
}