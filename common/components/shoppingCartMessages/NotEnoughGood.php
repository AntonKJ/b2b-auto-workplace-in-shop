<?php

namespace common\components\shoppingCartMessages;

class NotEnoughGood extends MessageBase implements IMessage
{

	protected $available;

	public function __construct(int $available, array $config = [])
	{
		$this->available = $available;
		parent::__construct($config);
	}

	public function getCode()
	{
		return 10;
	}

	public function getMessage()
	{
		return "На складе недостаточно товара! Доступно {$this->available} шт.";
	}
}
