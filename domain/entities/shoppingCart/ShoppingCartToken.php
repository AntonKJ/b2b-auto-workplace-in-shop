<?php

namespace domain\entities\shoppingCart;

use domain\entities\EntityBase;

/**
 * Class ShoppingCartToken
 * @package core\entities\shoppingCart
 */
class ShoppingCartToken extends EntityBase
{

	/**
	 * @var string
	 */
	private $token;

	/**
	 * ShoppingCartToken constructor.
	 */
	public function __construct()
	{
		$this->token = static::generateToken();
	}

	/**
	 * @param null $seed
	 * @return string
	 */
	static protected function generateToken($seed = null): string
	{
		return md5(time() . uniqid((string)$seed) . time());
	}

	/**
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	public function fields()
	{
		return [
			'token' => $this->getToken(),
		];
	}

	function __toString()
	{
		return $this->getToken();
	}

}