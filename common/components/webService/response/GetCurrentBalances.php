<?php

namespace common\components\webService\response;

class GetCurrentBalances extends BaseResponse
{
	private $_data;

	protected function prepareRow($data)
	{

		$data = str_replace(['[[[', ']]]'], '', $data);
		$data = explode("\t", $data);

		$out = [];
		foreach ($data as $row) {

			$row = explode(':', $row);
			$out[(int)$row[0] . '_0'] = [
				'shop_id' => (int)$row[0],
				'amount' => (int)preg_replace('/[^\d]/ui', '', $row[1]),
				'days' => 0,
				'from_shop_id' => (int)$row[0],
			];
		}

		return $out;
	}

	public function getStock()
	{

		$response = $this->getData();

		if (!is_array($response))
			$response = [$response];

		$data = [];
		foreach ($response as $row) {

			$data[$row->Good] = $this->prepareRow($row->ShopsAndCount);
		}

		return $data;
	}

	public function getData()
	{

		if ($this->_data === null) {
			$this->_data = $this->result->return->Balances ?? [];
		}

		return $this->_data;
	}
}