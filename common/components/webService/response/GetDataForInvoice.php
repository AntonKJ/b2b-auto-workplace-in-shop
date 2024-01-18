<?php

namespace common\components\webService\response;

class GetDataForInvoice extends BaseResponse
{
	private $_data;

	public function getStatus()
	{
		return (bool)($this->getData()->Successfully || false);
	}

	public function getErrors()
	{
		return $this->getData()->DescriptionErrors ?? null;
	}

	public function getInvoice()
	{

		if (!$this->getStatus())
			return null;

		return (array)$this->getData()->Invoice ?? [];
	}

	private function getData()
	{

		if ($this->_data === null)
			$this->_data = $this->result->return;

		return $this->_data;
	}
}