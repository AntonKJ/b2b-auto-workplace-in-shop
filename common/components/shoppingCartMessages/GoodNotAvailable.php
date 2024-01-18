<?php

namespace common\components\shoppingCartMessages;

class GoodNotAvailable extends MessageBase implements IMessage
{

	public function getCode()
	{
		return 20;
	}

	public function getMessage()
	{
		return 'Товар недоступен для заказа!';
	}

}