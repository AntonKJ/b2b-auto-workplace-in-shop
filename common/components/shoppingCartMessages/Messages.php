<?php

namespace common\components\shoppingCartMessages;

use yii\base\Component;

class Messages extends Component
{

	protected $messages = [];

	public function add(IMessage $message)
	{
		$this->messages[] = $message;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function toArray()
	{
		return array_map(function ($v) {
			return $v->toArray();
		}, $this->messages);
	}

}