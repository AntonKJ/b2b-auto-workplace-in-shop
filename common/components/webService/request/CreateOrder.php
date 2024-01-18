<?php

namespace common\components\webService\request;

class CreateOrder extends BaseRequest
{

	/**
	 * @var string
	 */
	public $Order;

	public function rules()
	{
		return [
			[['Order'], 'required'],
			[['Order'], 'safe'],
		];
	}

}