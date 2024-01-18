<?php

namespace common\components\shoppingCartMessages;

class LimitationOnMaxPiecesInAllOrders extends MessageBase implements IMessage
{

	protected $limitation;

	public function __construct(int $limitation, array $config = [])
	{
		$this->limitation = $limitation;
		parent::__construct($config);
	}

	public function getCode()
	{
		return 10;
	}

	public function getMessage()
	{
		return "Ограничение на максимальное кол-во товаров в заказах {$this->limitation} шт.";
	}
}
