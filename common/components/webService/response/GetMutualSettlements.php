<?php

namespace common\components\webService\response;

class GetMutualSettlements extends BaseResponse
{
	private $_data;

	public function getBalance()
	{
		return $this->getData() ?? 0;
	}

	public function getResult()
	{
		return [
			'balance' => $this->getBalance(),
		];
	}

	private function getData()
	{

		if ($this->_data === null)
			$this->_data = (float)$this->result->return;

		return $this->_data;
	}
}