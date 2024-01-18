<?php

namespace domain\entities\shoppingCart;

use domain\entities\shoppingCart\dto\ShoppingCartDto;

class ShoppingCartBuilder
{

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int|null
	 */
	protected $user_id;

	/**
	 * @var string|null
	 */
	protected $token;

	/**
	 * @var \DateTime
	 */
	protected $updated_at;

	/**
	 * @param int $id
	 * @return ShoppingCartBuilder
	 */
	public function withId(int $id): ShoppingCartBuilder
	{

		$this->id = $id;

		return $this;
	}

	/**
	 * @param $token
	 * @return ShoppingCartBuilder
	 */
	public function setToken(ShoppingCartToken $token)
	{

		$this->token = $token;

		$this->user_id = null;

		return $this;
	}

	/**
	 * @param int $userId
	 * @return ShoppingCartBuilder
	 */
	public function withUserId(int $userId)
	{

		$this->user_id = $userId;

		$this->token = null;

		return $this;
	}

	/**
	 * @param \DateTime $dt
	 * @return ShoppingCartBuilder
	 */
	public function withUpdatedAt(\DateTime $dt)
	{

		$this->updated_at = $dt;

		return $this;
	}

	/**
	 * @return ShoppingCartDto
	 */
	public function getDto()
	{

		$dto = new ShoppingCartDto();

		$dto->id = $this->id;
		$dto->user_id = $this->user_id;
		$dto->token = $this->token;
		$dto->updated_at = $this->updated_at;

		return $dto;
	}

}