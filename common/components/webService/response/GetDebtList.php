<?php

namespace common\components\webService\response;

use function is_object;

class GetDebtList extends BaseResponse
{
	private $_data;

	public function getItems() {
		return $this->getData();
	}

	private function getData()
	{
		if ($this->_data === null) {
			$this->_data = $this->result->return->ClientDebt ?? [];
		}
		if (is_object($this->_data)) {
			$this->_data = [$this->_data];
		}
		return $this->_data;
	}
}