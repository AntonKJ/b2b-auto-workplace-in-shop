<?php

namespace api\modules\vendor\modules\mosautoshina\models\forms;

use api\modules\vendor\modules\mosautoshina\components\address\Address;
use api\modules\vendor\modules\mosautoshina\components\Order;
use common\components\deliveries\DeliveryCityRegion;
use common\components\deliveries\DeliveryGoodCollection;
use common\components\Delivery;
use common\components\payments\PaymentInterface;
use common\interfaces\PoiInterface;
use common\models\OrderType;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use domain\services\Service1c;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class OrderForm
 * @package api\modules\vendor\modules\mosautoshina\models\forms
 */
class OrderForm extends Model
{

	const SHIPMENT_DATE_FORMAT = 'yyyy-MM-dd';

	public $addressId;
	public $paymentMethod;

	public $shipmentDate;
	public $shipmentMethod;

	public $comment;

	public $goods;

	protected $_orderComponent;

	/**
	 * @var GoodAvailabilityService
	 */
	protected $_availabilityComponent;

	/**
	 * @var Delivery
	 */
	protected $_deliveryComponent;

	protected $_customer;
	protected $_goods;
	protected $_addressModel;

	public function __construct(Order $component,
	                            GoodAvailabilityServiceInterface $availability,
	                            Delivery $delivery,
	                            array $config = [])
	{
		parent::__construct($config);

		$this->_orderComponent = $component;
		$this->_availabilityComponent = $availability;
		$this->_deliveryComponent = $delivery;
	}

	public function attributeLabels()
	{
		return [

		];
	}

	public function rules()
	{
		return [

			[['addressId'], 'required'],
			[['addressId'], 'in', 'range' => array_keys($this->_orderComponent->getAddressOptions()),
				'message' => 'Адрес с таким кодом «{value}» не существует.'],

			[['shipmentMethod'], 'trim'],
			[['shipmentMethod'], 'filter', 'filter' => 'mb_strtolower'],
			[['shipmentMethod'], 'compare', 'compareValue' => 'delivery'],

			[['paymentMethod'], 'trim'],
			[['paymentMethod'], 'filter', 'filter' => 'mb_strtolower'],
			[['paymentMethod'], 'in', 'range' => array_keys($this->_orderComponent->getPaymentMethodOptions())],

			[['comment'], 'trim'],
			[['comment'], 'string', 'length' => [0, 200]],

			[['goods'], 'required', 'message' => 'Укажите список товаров.'],
			[['goods'], 'validateGoodsData', 'skipOnError' => true],

			[['shipmentDate'], 'trim'],
			[['shipmentDate'], 'required', 'message' => 'Укажите дату самовывоза'],
			[['shipmentDate'], 'date', 'format' => static::SHIPMENT_DATE_FORMAT],

			// Валидация возможности заказа товаров для данного адреса
			[['addressId'], 'validateDeliveryZone'],
			[['goods'], 'validateGoodsAvailable', 'skipOnError' => true],
			[['shipmentDate'], 'validateDeliveryDate', 'skipOnError' => true],

		];
	}

	/**
	 * @return array|Good[]
	 */
	public function getGoodModels()
	{

		if ($this->_goods === null) {

			$this->_goods = [];
			foreach (array_keys($this->goods) as $i) {

				$good = new Good();
				$this->_goods[$i] = $good;
			}
		}

		return $this->_goods;
	}

	/**
	 * @return Address|null
	 */
	public function getAddressModel(): ?Address
	{
		if ($this->_addressModel === null)
			$this->_addressModel = ArrayHelper::getValue($this->_orderComponent->getAddressOptions(), $this->addressId, null);

		return $this->_addressModel;
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \yii\base\InvalidConfigException
	 */
	public function validateGoodsData($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		Model::loadMultiple($this->getGoodModels(), $this->{$attribute}, '');
		if (!Model::validateMultiple($this->getGoodModels())) {

			$errors = [];
			/** @var Good $model */
			foreach ($this->getGoodModels() as $i => $model) {

				if ($model->hasErrors())
					$errors[(string)$i] = $model->getErrors();
			}
			$this->addError('goods', (object)$errors);
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function validateGoodsAvailable($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		/**
		 * @var Service1c $service1c
		 */
		$service1c = \Yii::$container->get(Service1c::class);

		$goods = $this->getGoodsData();
		$zoneId = $this->getRegion()->getZoneId();

		// Получаем остатки для товаров в корзине
		$stocks = $service1c->getCurrentBalances(array_keys($goods));
		if (\is_array($stocks)) {

			/**
			 * @var GoodAvailabilityService $availability
			 */
			$availability = $this->_availabilityComponent;

			// Обновляем остатки
			foreach (array_keys($goods) as $goodId) {

				$stock = $stocks[$goodId] ?? [];
				$stock = array_merge($availability->getAvailablePreorderFromCache($goodId), $stock);

				$availability->updateCache($goodId, $zoneId, $stock);
			}

		}

		if ($this->getStoresForOrder() === [])
			$this->addError($attribute, 'Доступны не все заказанные позиции. Наличие товара изменилось.');
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator Validator
	 * @throws \yii\base\InvalidConfigException
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function validateDeliveryDate($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		$value = $this->{$attribute};

		if ($validator->skipOnEmpty && empty($value))
			return;

		$deliveryOptions = $this->getDeliveryType()
			->getDeliveryDaysDataByGeoPosition($this->getAddressModel()->getGeoPosition());

		if (null === $deliveryOptions) {

			$this->addError($attribute, 'Нет подходящих дат доставки для выбранного адреса.');
			return;
		}

		$minDt = new \DateTime($deliveryOptions['min']['dayDatetime']);
		$maxDt = new \DateTime($deliveryOptions['max']['dayDatetime']);

		$dt = $this->getDateAsDateTime();

		if (!($dt->getTimestamp() >= $minDt->getTimestamp() && $maxDt->getTimestamp() >= $dt->getTimestamp()))
			$this->addError($attribute, "Выберите правильную дату доставки между {$minDt->format('d.m.Y')} и {$maxDt->format('d.m.Y')}, Вы выбрали {$dt->format('d.m.Y')}");

		$dayOfTheWeek = (int)$dt->format('N');

		if (!isset($deliveryOptions['days'][$dayOfTheWeek])) {

			$days = [
				'понедельник',
				'вторник',
				'среду',
				'четверг',
				'пятницу',
				'субботу',
				'воскресение',
			];

			$errorMessage = ['Доставка осуществляется, только в определённые дни:'];
			$errorMessage[] = mb_strtolower(implode(', ', array_values($deliveryOptions['days']))) . '.';
			$errorMessage[] = 'Вы указали ' . $days[$dayOfTheWeek - 1];

			$this->addError($attribute, implode(' ', $errorMessage));
		}
	}

	protected function getDeliveryGoodCollection(): DeliveryGoodCollection
	{

		static $deliveryGoodCollection;

		if ($deliveryGoodCollection === null) {

			$deliveryGoodCollection = new DeliveryGoodCollection();
			/** @var Good $good */
			foreach ($this->getGoodsData() as $good)
				$deliveryGoodCollection->addGood($good->sku, $good->quantity);
		}

		return $deliveryGoodCollection;
	}

	protected function getDeliveryType(): DeliveryCityRegion
	{

		static $delivery;

		if ($delivery === null)
			$delivery = new DeliveryCityRegion($this->getRegion(), $this->getUser(), $this->getDeliveryGoodCollection());

		return $delivery;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateAsDateTime()
	{
		$date = (new \DateTime($this->shipmentDate))
			->setTimezone(new \DateTimeZone(\Yii::$app->timeZone));

		return $date;
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getStoresForOrder()
	{

		$zoneId = $this->getRegion()->getZoneId();

		return $this->_deliveryComponent
			->getStoresForGoods($this->getDeliveryGoodCollection()->getData(), $zoneId, OrderType::ORDER_TYPE_PICKUP, $this->getShop());
	}

	public function getGoodsData(): array
	{

		static $data;

		if ($data === null)
			$data = ArrayHelper::index($this->getGoodModels(), function (Good $v) {
				return $v->sku;
			});

		return $data;
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \Throwable
	 */
	public function validateDeliveryZone($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		if ($this->getOrderType() === null)
			$this->addError($attribute, 'Нет в наличии или недоступно.');
	}

	/**
	 * @throws \Throwable
	 */
	public function getOrderType(): ?OrderType
	{

		static $orderType = false;

		if (false === $orderType)
			$orderType = $this->getDeliveryType()->getOrderTypeByGeoPosition($this->getAddressModel()->getGeoPosition());

		return $orderType;
	}

	/**
	 * @throws \Throwable
	 */
	public function getClosestPoi(): ?PoiInterface
	{

		static $closestPoi = false;

		if (false === $closestPoi)
			$closestPoi = $this->getDeliveryType()->getClosestPoiByGeoPosition($this->getAddressModel()->getGeoPosition());

		return $closestPoi;
	}

	/**
	 * @throws \Throwable
	 */
	public function getShop(): int
	{
		return (int)$this->getOrderType()->from_shop_id;
	}

	public function getUser()
	{
		return $this->_orderComponent->getUser();
	}

	/**
	 * @return \common\interfaces\RegionEntityInterface
	 */
	public function getRegion()
	{
		return $this->getUser() ? $this->getUser()->region : null;
	}

	/**
	 * @return PaymentInterface
	 */
	public function getPaymentModel()
	{
		return $this->_orderComponent->getPaymentMethodOptions()[$this->paymentMethod];
	}

}