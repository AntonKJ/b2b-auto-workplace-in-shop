<?php

namespace api\modules\regular\components\ecommerce\models;

use api\modules\regular\models\ApiUser;
use common\interfaces\B2BUserInterface;
use myexample\ecommerce\payments\PaymentTypesTrait;
use myexample\ecommerce\UserInterface;

class UserAdapter implements UserInterface
{

	use PaymentTypesTrait;

	/**
	 * @var B2BUserInterface|ApiUser
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
