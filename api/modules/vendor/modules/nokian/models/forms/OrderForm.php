<?php

namespace api\modules\vendor\modules\nokian\models\forms;

use common\components\deliveries\DeliveryPickup;
use common\components\Delivery;
use common\components\payments\PaymentCash;
use common\models\OrderType;
use common\models\Shop;
use common\models\TyreGood;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use domain\services\Service1c;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class OrderForm
 * @package api\modules\vendor\modules\nokian\models\forms
 *
 * @property Customer $customer
 *
 */
class OrderForm extends Model
{

	const SHIPMENT_DATE_FORMAT = 'yyyy-MM-dd';

	public $entity;
	public $action;
	public $created;

	public $id;
	public $shopId;
	public $paymentMethod;
	public $comment;

	public $shipmentDate;
	public $shipmentMethod;

	public $customerUid;
	public $customerType;

	public $customerPerson;
	public $product;

	protected $_orderComponent;

	/**
	 * @var GoodAvailabilityService
	 */
	protected $_availabilityComponent;

	/**
	 * @var Delivery
	 */
	protected $_deliveryComponent;

	protected $_customerPerson;
	protected $_products;

	public function __construct(\api\modules\vendor\modules\nokian\components\Order $component, GoodAvailabilityServiceInterface $availability, Delivery $delivery, array $config = [])
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

			[['entity'], 'compare', 'compareValue' => 'ORDER'],
			[['action'], 'compare', 'compareValue' => 'CREATE'],

			[['created'], 'date', 'format' => 'php:Y-m-d H:i:s'],

			[['id'], 'string', 'length' => [0, 255]],

			[['shopId'], 'required'],
			[['shopId'], 'string', 'length' => [0, 255]],
			[['shopId'], 'validateShop', 'skipOnError' => true],

			[['shipmentMethod'], 'in', 'range' => [
				'PICKUP_ONLINE',
				'PICKUP_OFFLINE',
			]],
			[['paymentMethod'], 'compare', 'compareValue' => 'CASH'],

			[['comment'], 'string', 'length' => [0, 1000]],

			[['customerUid', 'customerType'], 'safe'],

			[['customerPerson'], 'required'],
			[['customerPerson'], 'validateCustomerPerson'],

			[['product'], 'required'],
			[['product'], 'filter', 'filter' => static function ($value) {
				if (empty($value)) {
					return null;
				}
				if (!is_array($value)) {
					$value = [$value];
				}
				if (!ArrayHelper::isIndexed($value)) {
					$value = [$value];
				}
				return $value;
			}],
			[['product'], 'validateProductsData', 'skipOnError' => true],
			[['product'], 'validateProductsAvailable', 'skipOnError' => true],

			[['shipmentDate'], 'required', 'message' => 'Укажите дату самовывоза'],
			[['shipmentDate'], 'date', 'format' => static::SHIPMENT_DATE_FORMAT],
			[['shipmentDate'], 'validateDeliveryDate', 'skipOnError' => true],

		];
	}

	/**
	 * @return Customer
	 */
	public function getCustomer()
	{
		return $this->getCustomerPersonModel();
	}

	/**
	 * @return Customer
	 */
	public function getCustomerPersonModel()
	{

		if ($this->_customerPerson === null)
			$this->_customerPerson = new Customer();

		return $this->_customerPerson;
	}

	public function getProductsModel()
	{

		if ($this->_products === null) {

			$this->_products = [];
			foreach (array_keys($this->product) as $i) {

				$product = new Product();
				$this->_products[$i] = $product;
			}
		}

		return $this->_products;
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \yii\base\InvalidConfigException
	 */
	public function validateProductsData($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		Model::loadMultiple($this->productsModel, $this->{$attribute}, '');
		Model::validateMultiple($this->productsModel);

		if (!Model::validateMultiple($this->productsModel)) {

			/** @var Model $model */
			foreach ($this->productsModel as $i => $model) {

				foreach ($model->getErrors() as $field => $messages)
					foreach ($messages as $message)
						$this->addError('product', $message);
			}
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator Validator
	 * @throws \yii\base\InvalidConfigException
	 * @throws \Exception
	 */
	public function validateDeliveryDate($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		$value = $this->{$attribute};

		if ($validator->skipOnEmpty && empty($value))
			return;

		$dt = $this->getDateAsDateTime();

		$deliveryOptions = $this->getShopDeliveryOptions();
		if (null === $deliveryOptions) {

			$this->addError($attribute, 'Нет подходящих дат для выбранного магазина.');
			return;
		}

		$dtMin = new \DateTime($deliveryOptions['deliveryDate']['min']['dayDatetime']);
		$dtMax = new \DateTime($deliveryOptions['deliveryDate']['max']['dayDatetime']);

		if (!($dt->getTimestamp() >= $dtMin->getTimestamp() && $dtMax->getTimestamp() >= $dt->getTimestamp()))
			$this->addError($attribute, "Выберите правильную дату самовывоза между {$dtMin->format('d.m.Y')} и {$dtMax->format('d.m.Y')}, Вы выбрали {$dt->format('d.m.Y')}");
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
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function validateProductsAvailable($attribute, $params, $validator)
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

	public function getStoresForOrder()
	{

		$goods = $this->getGoodsData();
		$zoneId = $this->getRegion()->getZoneId();

		return $this->_deliveryComponent
			->getStoresForGoods(array_values($goods), $zoneId, OrderType::ORDER_TYPE_PICKUP, $this->getShop()->getId());
	}

	/**
	 * @param array $codes
	 * @return array
	 */
	protected function getGoodsByCodes($codes)
	{

		static $data;

		$key = md5(implode(',', $codes));
		if (!isset($data[$key])) {

			$reader = TyreGood::find()
				->select([
					'id' => 'idx',
					'code' => 'manuf_code',
				])
				->byManufCode($codes)
				->asArray();

			$data[$key] = [];
			foreach ($reader->each() as $row)
				$data[$key][$row['code']] = $row['id'];
		}

		return $data[$key];
	}

	public function getGoodsData()
	{

		$goodsCodes = ArrayHelper::index($this->productsModel, function ($v) {
			return $v->code;
		});

		static $data;

		$key = md5(implode(',', array_keys($goodsCodes)));

		if (!isset($data[$key])) {

			$goodIds = $this->getGoodsByCodes(array_keys($goodsCodes));

			$data[$key] = [];
			foreach ($goodIds as $goodCode => $goodId) {

				if (!isset($goodsCodes[$goodCode]))
					continue;

				$data[$key][$goodId] = [
					'id' => $goodId,
					'quantity' => $goodsCodes[$goodCode]->quantity,
				];
			}
		}

		return $data[$key];
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \yii\base\InvalidConfigException
	 */
	public function validateCustomerPerson($attribute, $params, $validator)
	{

		$this->customerPersonModel->load($this->{$attribute}, '');
		$this->customerPersonModel->validate();

		if ($this->customerPersonModel->hasErrors()) {

			foreach ($this->customerPersonModel->getErrors() as $field => $messages)
				$this->addError("customerPerson", $messages);
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws \yii\base\InvalidConfigException
	 */
	public function validateShop($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		$shopId = $this->getMyexampleShopId();
		if ($shopId === null) {

			$this->addError((string)$attribute, 'Магазин не найден');
		}
	}

	/**
	 * @return int
	 */
	public function getMyexampleShopId()
	{
		return $this->_orderComponent->getShopIdByVendorShopId($this->shopId);
	}

	/**
	 * @return Shop|null
	 */
	public function getShop()
	{

		static $shop;
		if ($shop === null) {

			$shop = Shop::find()->byId($this->getMyexampleShopId())->one();
			if ($shop === null)
				$shop = false;
		}

		return $shop === false ? null : $shop;
	}

	/**
	 * @return array|null
	 * @throws \Exception
	 */
	public function getShopDeliveryOptions()
	{

		$goods = $this->getGoodsData();
		$zoneId = $this->getRegion()->getZoneId();

		$data = $this->_deliveryComponent
			->getPickupStoresForGoods($goods, $zoneId, OrderType::ORDER_TYPE_PICKUP);

		$shopId = $this->getShop()->getId();

		if (isset($data[$shopId]))
			return DeliveryPickup::prepareShopDeliveryData(new \DateTime(), $data[$shopId]);

		return null;
	}

	/**
	 * @return \common\interfaces\RegionEntityInterface
	 */
	public function getRegion()
	{
		return $this->getShop()->region;
	}

	/**
	 * @return PaymentCash
	 */
	public function getPaymentModel()
	{
		return new PaymentCash();
	}

}
