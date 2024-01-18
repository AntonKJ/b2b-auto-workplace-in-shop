<?php

namespace domain\entities\service1c;

use domain\interfaces\EntityCollectionInterface;

interface ClientDebtCollectionInterface extends EntityCollectionInterface
{
	public function add(ClientDebt $data, $key = null);
}