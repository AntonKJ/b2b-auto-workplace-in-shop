<?php

namespace common\components\deliveries\forms;

use common\models\OptUserAddress;
use common\models\OrderType;
use yii\base\InvalidCallException;
use yii\base\Model;
use yii\base\UnknownMethodException;
use yii\helpers\ArrayHelper;

class DeliveryRussiaTcForm extends Model implements DeliveryFormInterface
{

	use PaymentFormTrait;
	use DeliveryFormTrait;

	public $city;
	public $userCity;

	public $tc;
	public $userTc;

	public $payment;

	public $comment;

	protected $_cities;
	protected $_tc;
	protected $_payments;
	protected $_activeOrderTypes;

	public function __construct(array $cities, array $tc, array $payments, array $activeOrderTypes, array $config = [])
	{
		$this->_tc = $tc;
		$this->_cities = $cities;
		$this->_payments = ArrayHelper::index($payments, 'id');
		$this->_activeOrderTypes = $activeOrderTypes;

		parent::__construct($config);
	}

	public function getDateAsDateTime()
	{
		throw new UnknownMethodException('Method not implemented!');
	}

	/**
	 * @return OrderType
	 */
	public function getOrderType()
	{
		$orderType = reset($this->_activeOrderTypes);
		return $orderType;
	}

	public function getShopId()
	{
		return $this->getOrderType()->from_shop_id;
	}

	public function attributeLabels()
	{
		return [
			'city' => 'Город',
			'tc' => 'Транспортная компания',
			'payment' => 'Вариант оплаты',
			'comment' => 'Комментарий к заказу',
		];
	}

	public function getCitiesIdsOptions()
	{

		static $options = null;
		if (null === $options) {

			$options = [];
			foreach ($this->_cities as $itm)
				$options[$itm->id] = $itm;
		}

		return $options;
	}

	public function getTcIdsOptions()
	{

		static $options = null;
		if (null === $options) {

			$options = [];
			foreach ($this->_tc as $itm)
				$options[$itm['id']] = $itm;
		}

		return $options;
	}

	/**
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 */
	public function rules()
	{
		return [

			[['city'], 'required', 'message' => 'Выберите город доставки', 'when' => function ($model) {
				return empty($model->userCity);
			}],
			[['city'], 'in', 'range' => array_keys($this->getCitiesIdsOptions())],

			[['userCity'], 'required', 'message' => 'Заполните город доставки', 'when' => function ($model) {
				return empty($model->city);
			}],
			[['userCity'], 'filter', 'filter' => 'trim'],
			[['userCity'], 'string', 'max' => 128],

			[['tc'], 'required', 'message' => 'Выберите транспортную компанию', 'when' => function ($model) {
				return empty($model->userTc);
			}],
			[['tc'], 'in', 'range' => array_keys($this->getTcIdsOptions())],

			[['userTc'], 'required', 'message' => 'Укажите транспортную компанию', 'when' => function ($model) {
				return empty($model->tc);
			}],
			[['userTc'], 'filter', 'filter' => 'trim'],
			[['userTc'], 'string', 'max' => 128],

			[['payment'], 'required', 'message' => 'Выберите способ оплаты'],
			[['payment'], 'in', 'range' => array_keys($this->_payments)],

			[['comment'], 'filter', 'filter' => function ($value) {
				return trim(preg_replace('/\s{2,}/ui', ' ', \yii\helpers\HtmlPurifier::process($value)));
			}],
			[['comment'], 'string', 'max' => 1000],

		];
	}

	public function getCityText()
	{
		return !empty($this->userCity) ? $this->userCity : ArrayHelper::getValue($this->getCitiesIdsOptions(), "{$this->city}.city");
	}

	public function getTcText()
	{
		return !empty($this->userTc) ? $this->userTc : ArrayHelper::getValue($this->getTcIdsOptions(), "{$this->tc}.title");
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
		return null;
	}


}