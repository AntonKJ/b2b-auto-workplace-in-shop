<?php

namespace common\components\webService\response;

class GetOrdersByVendor extends BaseResponse
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

		return $this->_data;
	}
}