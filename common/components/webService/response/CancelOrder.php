<?php

namespace common\components\webService\response;

class CancelOrder extends BaseResponse
{
	private $_data;

	public function getStatus()
	{
		return (bool)($this->getData()->Successfully ?? false);
	}

	public function getMessage()
	{
		return $this->getData()->DescriptionErrors;
	}

	public function getResponce()
	{
		return [
			'status' => $this->getStatus(),
			'message' => $this->getMessage(),
		];
	}

	private function getData()
	{

		if ($this->_data === null)
			$this->_data = $this->result->return;

		return $this->_data;
	}
}