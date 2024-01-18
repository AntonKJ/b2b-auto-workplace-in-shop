<?php

namespace domain\entities\shoppingCart;

use domain\entities\EntityBase;

/**
 * Class ShoppingCartGood
 * @package core\entities\shoppingCart
 */
class ShoppingCartGood extends EntityBase
{

	/**
	 * @var
	 */
	private $id;

	/**
	 * @var int
	 */
	protected $cartId;

	/**
	 * @var string
	 */
	protected $entityType;

	/**
	 * @var string
	 */
	protected $entityId;

	/**
	 * @var int
	 */
	protected $quantity;

	/**
	 * @var float
	 */
	protected $price;

	/**
	 * ShoppingCartGood constructor.
	 * @param int $id
	 * @param int $cart
	 * @param string $entityType
	 * @param string $entityId
	 * @param int $quantity
	 * @param float $price
	 */
	public function __construct(int $id, ShoppingCart $cart, string $entityType, string $entityId, int $quantity, float $price)
	{
		$this->id = $id;
		$this->cartId = $cart->getId();
		$this->entityType = $entityType;
		$this->entityId = $entityId;
		$this->quantity = $quantity;
		$this->price = $price;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getCartId(): int
	{
		return $this->cartId;
	}

	/**
	 * @return string
	 */
	public function getEntityType(): string
	{
		return $this->entityType;
	}

	/**
	 * @return string
	 */
	public function getEntityId(): string
	{
		return $this->entityId;
	}

	/**
	 * @return int
	 */
	public function getQuantity(): int
	{
		return $this->quantity;
	}

	/**
	 * @return float
	 */
	public function getPrice(): float
	{
		return $this->price;
	}



}