<?php

namespace common\components\ecommerce;

use myexample\ecommerce\GoodIdentityInterface;

class GoodIdentity implements GoodIdentityInterface
{

	/**
	 * @var string
	 */
	protected $_goodId;

	public function __construct(string $goodId)
	{
		$this->_goodId = $goodId;
	}

	/**
	 * @return string
	 */
	public function getGoodId(): string
	{
		return $this->_goodId;
	}

	public function __toString()
	{
		return $this->_goodId;
	}

}
