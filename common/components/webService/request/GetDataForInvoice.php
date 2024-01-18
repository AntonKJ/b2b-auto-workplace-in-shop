<?php

namespace common\components\webService\request;

class GetDataForInvoice extends BaseRequest
{

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