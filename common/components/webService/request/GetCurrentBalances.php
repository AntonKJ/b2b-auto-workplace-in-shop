<?php

namespace common\components\webService\request;

class GetCurrentBalances extends BaseRequest
{

	/**
	 * @var string
	 */
	public $CodeGood;

	public function rules()
	{
		return [
			[['CodeGood'], 'required'],
			[['CodeGood'], 'string'],
		];
	}

}