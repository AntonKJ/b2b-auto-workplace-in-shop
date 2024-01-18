<?php

namespace domain\interfaces;

use domain\entities\shop\ShopStockEntityCollectionInterface;

interface ShopStockRepositoryInterface extends RepositoryInterface
{

	public function findAllByGoodId($id): ShopStockEntityCollectionInterface;

}