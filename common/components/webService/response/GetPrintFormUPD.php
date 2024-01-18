<?php

namespace common\components\webService\response;

class GetPrintFormUPD extends BaseResponse
{
	private $_data;

	public function getResponse()
	{
		return $this->getData();
	}

	private function getData()
	{

		if ($this->_data === null)
			$this->_data = $this->result->return;

		return $this->_data;
	}
}