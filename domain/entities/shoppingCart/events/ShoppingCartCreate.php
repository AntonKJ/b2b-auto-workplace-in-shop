<?php

namespace domain\entities\shoppingCart\events;

use domain\entities\shoppingCart\ShoppingCart;
use domain\interfaces\EventInterface;

class ShoppingCartCreate implements EventInterface
{

	public $cart;

	function __construct(ShoppingCart $cart)
	{
		$this->cart = $cart;
	}

}