<?php

namespace domain\interfaces;

use domain\entities\order\OrderTypeEntityCollectionInterface;

interface OrderTypeRepositoryInterface extends RepositoryInterface
{

	public function findAll(): OrderTypeEntityCollectionInterface;

}