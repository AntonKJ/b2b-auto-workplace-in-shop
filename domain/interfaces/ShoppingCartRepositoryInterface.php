<?php

namespace domain\interfaces;

use domain\entities\shoppingCart\collections\ShoppingCartGoodCollectionInterface;
use domain\entities\shoppingCart\dto\ShoppingCartDto;
use domain\entities\shoppingCart\ShoppingCart;

interface ShoppingCartRepositoryInterface extends RepositoryInterface
{

	public function findShoppingCartByUserId(int $userId): ShoppingCart;

	public function getShoppingCartByToken(string $token): ShoppingCart;

	public function findGoodsByShoppingCartId(int $cartId): ShoppingCartGoodCollectionInterface;

	public function create(ShoppingCartDto $data): ShoppingCart;

}