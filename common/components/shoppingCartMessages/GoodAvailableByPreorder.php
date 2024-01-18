<?php

namespace common\components\shoppingCartMessages;

class GoodAvailableByPreorder extends MessageBase implements IMessage
{

	public function getCode()
	{
		return 30;
	}

	public function getMessage()
	{
		return 'Товар доступен по предварительному заказу!';
	}

}