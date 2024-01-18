<?php

namespace api\modules\vendor\modules\cordiant\models\forms;

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
 * @package api\modules\vendor\modules\cordiant\models\forms
 */
class OrderForm extends Model
{

	const SHIPMENT_DATE_FORMAT = 'yyyy-MM-dd';

	public $shopId;
	public $paymentMethod;

	public $shipmentDate;
	public $shipmentMethod;

	public $comment;

	public $customer;
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

	public function __construct(\api\modules\vendor\modules\cordiant\components\Order $component,
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

			[['shopId'], 'required'],
			[['shopId'], 'exist', 'targetClass' => Shop::class,
				'targetAttribute' => 'shop_id', 'message' => 'Магазин «{value}» не существует.'],

			[['shipmentMethod'], 'compare', 'compareValue' => 'PICKUP'],
			[['paymentMethod'], 'compare', 'compareValue' => 'CASH'],

			[['comment'], 'string', 'length' => [0, 1000]],

			[['customer'], 'required'],
			[['customer'], 'validateCustomer'],

			[['goods'], 'required', 'message' => 'Укажите список товаров.'],
			[['goods'], 'filter', 'filter' => function ($value) {

				if (empty($value))
					return null;

				if (!\is_array($value))
					$value = [$value];

				if (!ArrayHelper::isIndexed($value))
					$value = [$value];

				return $value;
			}],
			[['goods'], 'validateGoodsData', 'skipOnError' => true],
			[['goods'], 'validateGoodsAvailable', 'skipOnError' => true],

			[['shipmentDate'], 'required', 'message' => 'Укажите дату самовывоза'],
			[['shipmentDate'], 'date', 'format' => static::SHIPMENT_DATE_FORMAT],
			[['shipmentDate'], 'validateDeliveryDate', 'skipOnError' => true],

		];
	}

	/**
	 * @return Customer
	 */
	public function getCustomerModel()
	{

		if ($this->_customer === null)
			$this->_customer = new Customer();

		return $this->_customer;
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

		$dtMin = (new \DateTime($deliveryOptions['deliveryDate']['min']['dayDatetime']));
		$dtMax = (new \DateTime($deliveryOptions['deliveryDate']['max']['dayDatetime']));

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

	public function getStoresForOrder()
	{

		$goods = $this->getGoodsData();
		$zoneId = $this->getRegion()->getZoneId();

		return $this->_deliveryComponent
			->getStoresForGoods(array_values($goods), $zoneId, OrderType::ORDER_TYPE_PICKUP, $this->getShop()->getId());
	}

	/**
	 * @param array $skus
	 * @return array
	 */
	protected function getGoodsBySkus(array $skus)
	{

		static $data;

		$key = md5(implode(',', $skus));
		if (!isset($data[$key])) {

			$reader = TyreGood::find()
				->select([
					'id' => 'idx',
					'sku' => 'manuf_code',
				])
				->byManufCode($skus)
				->asArray();

			$data[$key] = [];
			foreach ($reader->each() as $row)
				$data[$key][$row['sku']] = $row['id'];
		}

		return $data[$key];
	}

	public function getGoodsData()
	{

		$goodsSkus = ArrayHelper::index($this->getGoodModels(), function (Good $v) {
			return $v->sku;
		});

		static $data;

		//todo возможно нужна сортировка
		$key = md5(implode(',', array_keys($goodsSkus)));

		if (!isset($data[$key])) {

			$goodIds = $this->getGoodsBySkus(array_keys($goodsSkus));

			$data[$key] = [];
			foreach ($goodIds as $goodSku => $goodId) {

				if (!isset($goodsSkus[$goodSku]))
					continue;

				$data[$key][$goodId] = [
					'id' => $goodId,
					'quantity' => $goodsSkus[$goodSku]->quantity,
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
	public function validateCustomer($attribute, $params, $validator)
	{

		$this->getCustomerModel()->load($this->{$attribute}, '');
		$this->getCustomerModel()->validate();

		if ($this->getCustomerModel()->hasErrors())
			$this->addError('customer', $this->getCustomerModel()->getErrors());
	}

	/**
	 * @return Shop|null
	 */
	public function getShop()
	{

		static $shop;
		if ($shop === null) {

			$shop = Shop::find()->byId($this->shopId)->one();
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