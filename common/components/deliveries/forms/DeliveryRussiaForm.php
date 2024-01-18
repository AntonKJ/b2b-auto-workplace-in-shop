<?php

namespace common\components\deliveries\forms;

use common\models\OptUserAddress;
use common\models\OrderType;
use yii\base\InvalidCallException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class DeliveryRussiaForm extends Model implements DeliveryFormInterface
{

	use PaymentFormTrait;
	use DeliveryFormTrait;

	public $city;
	public $street;
	public $house;

	public $date;
	public $payment;

	public $comment;
	public $schedule;

	protected $_cities;
	protected $_payments;
	protected $_schedules;
	protected $_activeOrderTypes;

	/**
	 * DeliveryRussiaForm constructor.
	 * @param array $cities
	 * @param array $payments
	 * @param array $activeOrderTypes
	 * @param array $schedules
	 * @param array $config
	 */
	public function __construct(array $cities, array $payments, array $activeOrderTypes, array $schedules, array $config = [])
	{
		$this->_cities = $cities;

		$this->_payments = ArrayHelper::index($payments, 'id');
		$this->_schedules = $schedules;

		$this->_activeOrderTypes = ArrayHelper::index($activeOrderTypes, 'id');

		parent::__construct($config);
	}

	/**
	 * @return OrderType
	 */
	public function getOrderType()
	{
		$city = $this->getCitiesIdsOptions()[$this->city] ?? null;
		return ArrayHelper::getValue($this->_activeOrderTypes, $city['orderTypeId'], null);
	}

	public function getShopId()
	{
		return $this->getOrderType()->from_shop_id;
	}

	public function attributeLabels()
	{
		return [
			'city' => 'Город',
			'street' => 'Улица',
			'house' => 'Дом/строение',
			'date' => 'Дата доставки',
			'payment' => 'Вариант оплаты',
			'comment' => 'Комментарий к заказу',
			'schedule' => 'Время доставки',
		];
	}

	public function getCitiesIdsOptions()
	{

		static $options = null;
		if (null === $options)
			$options = ArrayHelper::index($this->_cities, 'id');

		return $options;
	}

	public function getCityText()
	{

		$options = static::getCitiesIdsOptions();
		return ArrayHelper::getValue($options, "{$this->city}.title", null);
	}

	public function getFullAddress()
	{
		return implode(', ', [$this->getCityText(), $this->street, $this->house]);
	}

	/**
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 */
	public function rules()
	{
		return [

			[['city', 'street', 'house', 'comment', 'date'], 'trim'],

			[['city'], 'required', 'message' => 'Выберите город доставки'],
			[['city'], 'in', 'range' => array_keys($this->getCitiesIdsOptions())],

			[['comment'], 'string', 'max' => 1000],

			[['street'], 'filter', 'filter' => 'trim'],
			[['street'], 'required', 'message' => 'Укажите улицу'],
			[['street'], 'string', 'max' => 128],

			[['house'], 'filter', 'filter' => 'trim'],
			[['house'], 'required', 'message' => 'Укажите номер дома/строения'],
			[['house'], 'string', 'max' => 16],

			[['date'], 'required', 'message' => 'Укажите дату доставки'],
			[['date'], 'datetime', 'format' => static::JS_DATE_FORMAT],
			[['date'], 'validateDeliveryDate', 'skipOnError' => true],

			[['payment'], 'required', 'message' => 'Выберите способ оплаты'],
			[['payment'], 'in', 'range' => array_keys($this->_payments)],

			[['schedule'], 'validateDeliverySchedule'],

		];
	}

	public function validateDeliverySchedule($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		$value = $this->{$attribute};
		if (empty($value))
			return;

		$scheduleOptions = [];

		/** @var OrderType $orderType */
		$orderType = $this->getOrderType();
		if ($orderType !== null)
			$scheduleOptions = ArrayHelper::getColumn(ArrayHelper::getValue($this->_schedules, $orderType->getId(), []), 'id');

		if (!\in_array($value, $scheduleOptions))
			$this->addError($attribute, "Неверное значение `{$this->getAttributeLabel($attribute)}`.");
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \yii\base\InvalidConfigException
	 */
	public function validateDeliveryDate($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		$dt = $this->getDateAsDateTime();

		$dtTimestamp = $dt->getTimestamp();

		$city = $this->getCitiesIdsOptions()[$this->city];

		$dtMin = (new \DateTime($city['deliveryDate']['min']['dayDatetime']))->getTimestamp();
		$dtMax = (new \DateTime($city['deliveryDate']['max']['dayDatetime']))->getTimestamp();

		if (!($dtTimestamp >= $dtMin && $dtMax >= $dtTimestamp) || !isset($city['delivery']['days'][$dt->format('N')]))
			$this->addError($attribute, 'Выберите правильную дату доставки');
	}

	public function isAllowedAddressStore(): bool
	{
		return false;
	}

	public function loadAddressAttributes(OptUserAddress $addressModel): void
	{
		throw new InvalidCallException('Method not implemented.');
	}

	public function getScheduleModel()
	{
		/** @var OrderType $orderType */
		$orderType = $this->getOrderType();
		if ($orderType === null)
			return null;

		$scheduleOptions = ArrayHelper::index($orderType->getDeliverySchedule(), 'id');
		return ArrayHelper::getValue($scheduleOptions, $this->schedule, null);
	}


}