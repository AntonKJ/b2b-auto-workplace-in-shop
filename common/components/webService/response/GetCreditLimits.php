<?php

namespace common\components\webService\response;

class GetCreditLimits extends BaseResponse
{
	private $_data;

	public function getLimit()
	{
		return $this->getData()->ClientData->Sum ?? 0;
	}

	public function getCurrency()
	{
		return $this->getData()->ClientData->Currency ?? null;
	}

	public function getResult() {
		return [
			'limit' => $this->getLimit(),
			'currency' => $this->getCurrency(),
		];
	}

	private function getData()
	{

		if ($this->_data === null)
			$this->_data = $this->result->return;

		return $this->_data;
	}
}