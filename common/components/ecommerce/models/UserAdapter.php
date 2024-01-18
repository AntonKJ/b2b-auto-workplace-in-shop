<?php

namespace common\components\ecommerce\models;

use common\interfaces\B2BUserInterface;
use common\models\OptUser;
use myexample\ecommerce\payments\PaymentTypesTrait;
use myexample\ecommerce\UserInterface;

class UserAdapter implements UserInterface
{

	use PaymentTypesTrait;

	/**
	 * @var B2BUserInterface|OptUser
	 */
	protected $_user;

	public function __construct(B2BUserInterface $user)
	{
		$this->_user = $user;
	}

	public function getOrderTypeGroupId(): ?int
	{
		return $this->_user->getOrderTypeGroupId();
	}

	public function getPaymentTypeMask(): int
	{
		return $this->_user->getPaymentTypeMask();
	}

	public function getId(): int
	{
		return $this->_user->getId();
	}

	public function getEmail(): string
	{
		return $this->_user->getEmail();
	}

	public function getClientCode(): string
	{
		return $this->_user->getClientCode();
	}

	public function getCategoryId(): int
	{
		return $this->_user->getCategoryId();
	}

}
