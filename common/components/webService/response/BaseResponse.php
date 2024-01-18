<?php

namespace common\components\webService\response;

abstract class BaseResponse
{
	public $result;

	public function __construct($result)
	{
		$this->result = $result;
	}
}