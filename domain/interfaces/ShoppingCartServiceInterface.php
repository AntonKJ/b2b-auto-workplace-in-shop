<?php

namespace domain\interfaces;

use domain\entities\shoppingCart\collections\ShoppingCartGoodCollectionInterface;
use domain\entities\shoppingCart\ShoppingCart;
use domain\entities\shoppingCart\ShoppingCartGood;

interface ShoppingCartServiceInterface extends ServiceInterface
{

	public function getShoppingCartByUserId(int $userId): ShoppingCart;

	public function getGoodsByShoppingCart(ShoppingCart $cart): ShoppingCartGoodCollectionInterface;

	public function create($userId = null): ShoppingCart;

	public function addGoodToCart(ShoppingCartGood $good, ShoppingCart $cart): ShoppingCartGood;

}