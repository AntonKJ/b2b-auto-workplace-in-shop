<?php

namespace domain\interfaces;

use domain\entities\shop\ShopNetworkEntityCollectionInterface;

interface ShopNetworkRepositoryInterface extends RepositoryInterface
{

	public function findAll(): ShopNetworkEntityCollectionInterface;

}