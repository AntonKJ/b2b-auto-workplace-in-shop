<?php

namespace domain\interfaces;

use domain\entities\shop\ShopGroupMoveEntityCollectionInterface;

interface ShopGroupMoveRepositoryInterface extends RepositoryInterface
{

	public function findAll(): ShopGroupMoveEntityCollectionInterface;

}