<?php

namespace common\components\webService\response;

class GetBonusPoints extends BaseResponse
{
	private $_data;

	public function getBonusPoints()
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