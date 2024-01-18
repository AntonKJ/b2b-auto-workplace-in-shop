<?php

namespace common\components\webService\response;

class CreateOrder extends BaseResponse
{
	private $_data;
	private $_response;
	private $_errors;

	public function getResponseRaw()
	{
		return $this->getData();
	}

	protected function parseResponse($data, $refresh = false)
	{

		if ($this->_response === null || $refresh) {

			$data = mb_convert_encoding($data, 'utf8');
			$responseContent = explode("\n", $data);

			$reserv = [
				'id' => null,
				'did' => null,
				'invoice' => null,
				'cid' => null,
				'cid_state' => null,
				'shop' => null,
				'shop_state' => null,
				'bik_state' => null,
				'ccpay_id' => null,
				'items' => [],
				'errors' => [],
				'response_raw' => $data,
			];

			$data = [];
			foreach ($responseContent as $row) {

				$parts = explode(":#:", $row);

				if (count($parts) != 2)
					continue;

				$key = trim($parts[0]);
				$value = trim($parts[1]);

				$data[$key] = $value;

				switch ($key) {

					case 'Номер резерва':

						$reserv['id'] = ltrim($value, '0');
						break;

					case 'Постоянный клиент':

						if ($value == 'Х')
							$reserv['errors'][] = 'Проблема с номером клиента.';
						else
							list($reserv['cid'], $reserv['cid_state']) = explode("\t", $value);

						break;

					case 'Ответ':

						if ($value == 'ERROR')
							$reserv['errors'][] = 'Наличие товара изменилось. Пожалуйста, сообщите о данной проблеме по телефону.';
						break;

					case 'Соглашение':

						//if ($value == 'ERROR')
						$reserv['errors'][] = 'Не указано соглашение в 1С: ' . $value;
						break;

					case 'Номер счета':

						$reserv['invoice'] = preg_replace("/\D+0+(\d+)/", '\1', $value);

						$invoiceContent = [];
						$not_invoice_yet = true;

						foreach ($responseContent as $s) {

							if ($not_invoice_yet) {

								if (trim($s) === '#####') {
									$not_invoice_yet = false;
								}

								continue;
							}

							$invoiceContent[] = $s;
						}

						// Парсим счёт
						if ([] !== $invoiceContent) {

							$columnNames = [
								'SumNDS', 'Sum', 'SumInWords', 'Volume', 'Organization', 'Contractor',
								'Buyer', 'BankBik', 'BankName', 'SettlementAccount', 'CorrespondentAccount', 'AccountNumber',
							];

							$index = 0;

							// Пропускаем первую строку
							$invoiceContent = \array_slice($invoiceContent, 1);

							$invoiceData = [];
							foreach ($invoiceContent as $rowNum => $rowValue) {

								$columns = explode("\t", $rowValue);

								if ($index === 0 && mb_strtoupper($columns[0]) !== 'НДС') {

									if (!isset($invoiceData['Goods']))
										$invoiceData['Goods'] = [];

									$invoiceData['Goods'][] = [
										'Number' => $columns[0],
										'Name' => $columns[1],
										'UnitOfMeasurement' => $columns[2],
										'Count' => $columns[3],
										'Price' => $columns[4],
										'Sum' => $columns[5],
									];

									continue;
								}

								$invoiceData[$columnNames[$index]] = $columns[1] ?? null;
								$index++;

								if ($index >= \count($columnNames))
									break;
							}
						}

						$reserv['invoice_content'] = $invoiceContent;
						$reserv['invoice_content_processed'] = $invoiceData;
						break;

					case 'Номер Заказа':

						$reserv['did'] = 'О' . $value;
						break;

					case 'Магазин':

						if ($value = 'Х')
							$reserv['shop_state'] = -1;
						break;

					case 'БИК банка':

						list($reserv['bik'], $reserv['bik_state']) = explode("\t", $value);
						break;

					case 'Товар':

						list($item, $cnt1, $shopid, $cnt2, $shopstate)
							= array_pad(array_map('trim', explode("\t", $value)), 5, null);

						$reserv['items'][$item] = [
							'id' => $item,
							'cnt1' => $cnt1,
							'shopid' => $shopid,
							'cnt2' => $cnt2,
							'shopstate' => $shopstate,
						];

						if ((int)$cnt1 < 0) {

							$reserv['items'][$item]['cnt2'] = 0;
							$reserv['errors'][] = 'Доступны не все заказанные позиции.';
						}

						break;

					case 'Контрагент найден':

						$reserv['ok'] = 1;
						break;

					case 'Товар в резерве':

						break;

					default:

						$reserv['errors'][$key] = $value;
						break;
				}

				if ($reserv['errors'] !== [])
					return $reserv;
			}

			if (isset($reserv['invoice_content_processed'])) {

				if (!isset($reserv['invoice_content_processed']['AccountNumber']) || empty($reserv['invoice_content_processed']['AccountNumber']))
					if (isset($reserv['invoice']) && !empty($reserv['invoice']))
						$reserv['invoice_content_processed']['AccountNumber'] = $reserv['invoice'];
			}

			$reserv['ccpay_id'] = date('d.m.Y') . ' ' . $reserv['id'];
		}

		return $reserv;
	}

	public function getResult()
	{
		return $this->parseResponse($this->getData());
	}

	public function getStatus()
	{

		$data = $this->parseResponse($this->getData());
		return $data['errors'] === [];
	}

	public function getErrors()
	{
		$data = $this->parseResponse($this->getData());
		return $data['errors'];
	}

	public function getData()
	{

		if ($this->_data === null) {

			$this->_data = $this->result->return;
		}

		return $this->_data;
	}
}