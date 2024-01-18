<?php

namespace common\components\shoppingCartMessages;

use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;

abstract class MessageBase extends Component implements IMessage, Arrayable
{

	use ArrayableTrait;

	abstract public function getCode();

	abstract public function getMessage();

	public function fields()
	{
		return [
			'code',
			'message',
		];
	}

}
