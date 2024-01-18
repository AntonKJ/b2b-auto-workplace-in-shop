<?php

namespace common\components\webService\request;

class GetDataOrder extends BaseRequest
{

	/**
	 * @var string
	 */
	public $ClientCode;

	/**
	 * @var string
	 */
	public $OrderNumber;

	public function rules()
	{
		return [
			[['OrderNumber'], 'required'],
			[['OrderNumber'], 'string', 'max' => 16],
		];
	}

}