<?php

namespace common\components\webService\response;

class GetListOrders extends BaseResponse
{
	private $_data;

	public function getOrders()
	{
		return $this->getData();
	}

	private function getData()
	{

		if ($this->_data === null)
			$this->_data = $this->result->return->Order ?? [];

		if (\is_object($this->_data)) {

			$this->_data = [$this->_data];
		}

		return $this->_data;
	}
}