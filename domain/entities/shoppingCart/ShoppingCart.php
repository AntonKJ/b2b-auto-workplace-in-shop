<?php

namespace domain\entities\shoppingCart;

use domain\entities\EntityBase;
use domain\entities\shoppingCart\collections\ShoppingCartGoodCollection;
use domain\entities\shoppingCart\collections\ShoppingCartGoodCollectionInterface;
use domain\entities\shoppingCart\dto\ShoppingCartDto;
use domain\entities\shoppingCart\events\ShoppingCartCreate;
use domain\traits\EventTrait;

/**
 * Class ShoppingCart
 * @package core\entities\shoppingCart
 */
class ShoppingCart extends EntityBase
{

	use EventTrait;

	private $id;

	/**
	 * @var int|null
	 */
	protected $userId;

	/**
	 * @var ShoppingCartToken
	 */
	protected $token;

	/**
	 * @var \DateTime
	 */
	protected $updatedAt;

	/**
	 * @var ShoppingCartGoodCollectionInterface
	 */
	protected $items;

	public function __construct(ShoppingCartDto $data)
	{

		$this->id = $data->id;
		$this->userId = $data->user_id;
		$this->updatedAt = $data->updated_at;

		$this->token = $data->token;

		if ($data->items instanceof ShoppingCartGoodCollectionInterface)
			$this->items = $data->items;
		elseif (is_array($data->items))
			$this->items = new ShoppingCartGoodCollection($data->items);
		else
			$this->items = new ShoppingCartGoodCollection();

		$this->recordEvent(new ShoppingCartCreate($this));
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return int|null
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	public function assignToUserId(int $userId)
	{

		$this->userId = $userId;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdatedAt(): \DateTime
	{
		return $this->updatedAt;
	}

	public function fields()
	{
		return [
			'id' => $this->getId(),
			'userId' => $this->getUserId(),
			'updatedAt' => $this->getUpdatedAt()->getTimestamp(),
			'items' => $this->items,
		];
	}

}