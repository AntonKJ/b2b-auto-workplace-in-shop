<?php

namespace domain\interfaces;

use domain\entities\shop\Shop;
use domain\entities\shop\ShopAndGroupEntityCollectionInterface;
use domain\entities\shop\ShopEntityCollectionInterface;

interface ShopRepositoryInterface extends RepositoryInterface
{

	public function findAllShopsAndGroups(): ShopAndGroupEntityCollectionInterface;

	public function findOneBySpecification(ShopSpecificationInterface $specification): Shop;

	public function findAllBySpecification(ShopSpecificationInterface $specification): ShopEntityCollectionInterface;

}