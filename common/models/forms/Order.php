<?php

namespace common\models\forms;

use common\components\order\OrderGoodItem;
use yii\base\Model;

class Order extends Model
{

	//opf
	const COUNTERPARTY_LOOK_FIZIK = 0;
	const COUNTERPARTY_LOOK_URIK = 1;

	const COUNTERPARTY_TYPE_ORGANIZATION = 1;
	const COUNTERPARTY_TYPE_CLIENT = 2;
	const COUNTERPARTY_TYPE_ANONIMOUS = 3;

	//todo сделать нормальное именование
	const SALE_TYPE_NALICHNIE = 1;
	const SALE_TYPE_BEZNAL = 2;
	const SALE_TYPE_KARTA = 3;

	/**
	 * Список товаров
	 * @var array
	 */
	protected $_goods;

	/**
	 * Номер заказа в формате UUID::v4
	 * @var string
	 */
	public $orderId;

	/**
	 * ID региона
	 * @var string
	 */
	public $regionId;

	/**
	 * Тип контрагента
	 * @var int
	 */
	public $counterpartyType;

	/**
	 * Вид контрагента
	 * @var int
	 */
	public $counterpartyLook;

	/**
	 * Полное наименование контрагента
	 * @var string
	 */
	public $counterpartyFullName;

	/**
	 * Наименование контрагента
	 * @var string
	 */
	public $counterpartyName;

	/**
	 * Юридический адрес
	 * @var string
	 */
	public $legalAddress;

	/**
	 * ИНН/КПП
	 * @var string
	 */
	public $innKpp;

	/**
	 * БИК банка
	 * @var string
	 */
	public $bankBIC;

	/**
	 * Расчетный счет
	 * @var string
	 */
	public $paymentAccount;

	/**
	 * Тип продажи
	 * @var int
	 */
	public $saleType;

	/**
	 * Формировать резерв
	 * @var bool
	 */
	public $reserveForm;

	/**
	 * Формировать счёт
	 * @var bool
	 */
	public $invoiceForm;

	/**
	 * Формировать доставку
	 * @var bool
	 */
	public $deliveryForm;

	/**
	 * Доставка по России
	 * @var bool
	 */
	public $deliveryRussia;

	/**
	 * Магазин
	 * @var int
	 */
	public $shopId;

	/**
	 * Дата окончания резерва
	 * @var \DateTime
	 */
	public $reserveEndDt;

	/**
	 * Комментарий резерва
	 * @var string
	 */
	public $reserveComment;

	/**
	 * Адрес доставки
	 * @var string
	 */
	public $deliveryAddress;

	/**
	 * Фактический адрес
	 * @var string
	 */
	public $actualAddress;

	/**
	 * Дата доставки
	 * @var \DateTime
	 */
	public $deliveryDt;

	/**
	 * Дни доставки
	 * @var int
	 */
	public $deliveryDays;

	/**
	 * Комментарий доставки
	 * @var string
	 */
	public $deliveryComment;

	/**
	 * Транспортная компания (Доставка ТК)
	 * @var string
	 */
	public $deliveryTc;

	/**
	 * Пункт назначения
	 * @var string
	 */
	public $destination;

	/**
	 * Постоянный клиент
	 * @var string
	 */
	public $clientCode;

	/**
	 * Телефоны
	 * @var array
	 */
	public $phones;

	/**
	 * Телефон для SMS
	 * @var array
	 */
	public $smsPhone;

	/**
	 * Адрес эл. почты
	 * @var string
	 */
	public $email;

	public $vendor;
	public $vendorContent;

	/**
	 * Комментарий клиента
	 * @var string
	 */
	public $clientComment;

	/**
	 * Интервал доставки (9-18, 18-24)
	 * @var
	 */
	public $deliveryInterval;

	/**
	 * @var bool требуется перемещение
	 */
	protected $_movingRequired;

	/**
	 * @return bool
	 */
	public function isMovingRequired(): bool
	{
		return (bool)$this->_movingRequired;
	}

	/**
	 * @param bool $movingRequired
	 * @return Order
	 */
	public function setMovingRequired(bool $movingRequired): Order
	{
		$this->_movingRequired = $movingRequired;
		return $this;
	}

	/**
	 * @param OrderGoodItem $good
	 * @return $this
	 */
	public function addGood(OrderGoodItem $good)
	{

		$this->_goods[] = $good;
		return $this;
	}

	public function attributeLabels()
	{
		return [
			'orderId' => '# Заказа',
			'regionId' => 'Регион',
			//
			'counterpartyType' => 'Тип контрагента',
			'counterpartyLook' => 'Вид контрагента',
			'counterpartyFullName' => 'Полное наименование контрагента',
			'counterpartyName' => 'Наименование контрагента',
			//
			'legalAddress' => 'Юридический адрес',
			'innKpp' => 'ИНН/КПП',
			'bankBIC' => 'БИК банка',
			'paymentAccount' => 'Расчетный счет',
			'saleType' => 'Тип продажи',
			'reserveForm' => 'Формировать резерв',
			'invoiceForm' => 'Формировать счет',
			'deliveryForm' => 'Формировать доставку',
			'deliveryRussia' => 'Доставка по России',
			'shopId' => 'Магазин',
			'reserveEndDt' => 'Дата окончания резерва',
			'reserveComment' => 'Комментарий резерва',
			'deliveryAddress' => 'Адрес доставки',
			'actualAddress' => 'Фактический адрес',
			'deliveryDays' => 'Дни доставки',
			'deliveryDt' => 'Дата доставки',
			'deliveryComment' => 'Комментарий доставки',
			'deliveryInterval' => 'Интервал доставки',
			'deliveryTc' => 'Доставка ТК',
			'destination' => 'Пункт назначения',
			'clientCode' => 'Постоянный клиент',
			'phones' => 'Телефоны',
			'smsPhone' => 'Телефон для SMS',
			'email' => 'E-Mail',

			'clientComment' => 'Комментарий клиента',

			'vendor' => 'vendor',
			'vendorContent' => 'vendor-content',
		];
	}

	public function fields()
	{
		$fields = parent::fields();

		$fields['smsPhone'] = static function (self $model) {

			if ((string)$model->smsPhone === '' || mb_strpos((string)$model->smsPhone, '+7') === 0) {
				return $model->smsPhone;
			}

			return "+7{$model->smsPhone}";
		};

		$fields['reserveForm'] = static function ($model) {
			return (int)$model->reserveForm;
		};

		$fields['invoiceForm'] = static function ($model) {
			return (int)$model->invoiceForm;
		};

		$fields['deliveryForm'] = static function ($model) {
			return (int)$model->deliveryForm;
		};

		return $fields;
	}

	public function get1cDataArray()
	{

		$data = [];
		foreach ($this->toArray() as $field => $value) {

			if (!empty($value))
				$data[$this->getAttributeLabel($field)] = $value;
		}

		return $data;
	}

	public function prepareDataToSend()
	{

		$msg = [];
		foreach ($this->get1cDataArray() as $f => $v) {
			$msg[] = "{$f}:#:{$v}";
		}

		foreach ($this->_goods as $good)
			$msg[] = (string)$good;

		return implode("\r\n", $msg);
	}

}
