<?php

namespace api\modules\regular\models\forms;

use api\modules\regular\components\ecommerce\GoodEntity;
use api\modules\regular\components\ecommerce\GoodIdentity;
use api\modules\regular\components\ecommerce\models\RegionAdapter;
use api\modules\regular\components\ecommerce\models\UserAdapter;
use common\interfaces\B2BUserInterface;
use common\interfaces\RegionEntityInterface;
use common\models\OptUserAddress;
use DateTime;
use ReflectionException;
use myexample\ecommerce\customers\CustomerB2BClient;
use myexample\ecommerce\deliveries\DeliveryCityRegion;
use myexample\ecommerce\deliveries\DeliveryPickup;
use myexample\ecommerce\deliveries\DeliveryRussiaTc;
use myexample\ecommerce\EcommerceInterface;
use myexample\ecommerce\GoodCollection;
use myexample\ecommerce\OrderB2BApiForm;
use myexample\ecommerce\service1c\entities\OrderReserve;
use myexample\ecommerce\UserInterface;
use Throwable;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use function count;
use function is_array;

/**
 * Class OrderForm
 * @package api\modules\regular\models\forms
 */
class OrderForm extends Model
{

	public const SHIPMENT_DATE_FORMAT = 'yyyy-MM-dd';

	public $goods;

	public $shipmentMethod;

	public $shipmentShopId;
	public $shipmentAddressId;

	/**
	 * @var string транспортная компания
	 */
	public $shipmentTcId;

	/**
	 * @var string город назначения для транспортной компании
	 */
	public $shipmentCity;

	public $shipmentDate;

	public $paymentMethod;

	public $comment;

	/**
	 * @var EcommerceInterface
	 */
	protected $_ecommerce;

	/**
	 * @var \myexample\ecommerce\RegionEntityInterface
	 */
	protected $_region;

	/**
	 * @var UserInterface
	 */
	protected $_user;

	protected $_orderB2bForm;

	protected $_customer;
	protected $_goods;
	protected $_addressModel;

	public function __construct(EcommerceInterface $ecommerce,
	                            RegionEntityInterface $region,
	                            B2BUserInterface $user,
	                            array $config = [])
	{
		parent::__construct($config);

		$this->_ecommerce = $ecommerce;

		$this->_region = new RegionAdapter($region);
		$this->_user = new UserAdapter($user);
	}

	protected function getEcommerceOrderForm(): OrderB2BApiForm
	{

		if (null === $this->_orderB2bForm) {

			$goodCollection = new GoodCollection();

			/** @var Good $good */
			foreach ($this->getGoodModels() as $good) {

				$goodEntity = new GoodEntity(new GoodIdentity($good->sku), $good->quantity);
				$goodCollection->add($goodEntity);
			}

			$this->_orderB2bForm = new OrderB2BApiForm($this->_ecommerce, $goodCollection, $this->_region, $this->_user);
		}

		return $this->_orderB2bForm;
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return [
		];
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	public function rules()
	{
		return [

			[['goods'], 'required', 'message' => 'Укажите список товаров.'],
			[['goods'], 'validateGoodsData', 'skipOnError' => true],

			[['paymentMethod'], 'trim'],
			[['paymentMethod'], 'filter', 'filter' => 'mb_strtolower'],
			[['paymentMethod'], 'required', 'message' => 'Укажите способ оплаты заказа.'],

			[['shipmentMethod'], 'trim'],
			[['shipmentMethod'], 'filter', 'filter' => 'mb_strtolower'],
			[['shipmentMethod'], 'required', 'message' => 'Укажите тип получения заказа.'],

			[['shipmentDate'], 'trim'],
			[['shipmentDate'], 'required', 'message' => 'Укажите желаемую дату получения заказа.', 'when' => function () {
				return $this->shipmentMethod !== DeliveryRussiaTc::getCategory();
			}],
			[['shipmentDate'], 'date', 'format' => static::SHIPMENT_DATE_FORMAT],

			[['shipmentShopId'], 'trim'],
			[['shipmentShopId'], 'required', 'message' => 'Укажите магазин.', 'when' => function () {
				return $this->shipmentMethod === DeliveryPickup::getCategory();
			}],

			[['shipmentAddressId'], 'trim'],
			[['shipmentAddressId'], 'required', 'message' => 'Укажите идентификатор адреса доставки.', 'when' => function () {
				return $this->shipmentMethod === DeliveryCityRegion::getCategory();
			}],

			[['shipmentAddressId'], 'in', 'range' => array_keys($this->getAddressOptions()),
				'message' => 'Адрес с таким идентификатором «{value}» не существует.'],

			[['shipmentTcId'], 'trim'],
			[['shipmentTcId'], 'required', 'message' => 'Укажите желаемую транспортную компанию.', 'when' => function () {
				return $this->shipmentMethod === DeliveryRussiaTc::getCategory();
			}],
			[['shipmentTcId'], 'in', 'range' => array_keys($this->getTcOptions()),
				'message' => 'Транспортной компании с таким идентификатором «{value}» не существует.'],

			[['shipmentCity'], 'trim'],
			[['shipmentCity'], 'required', 'message' => 'Укажите желаемую город доставки для транспортной компании.', 'when' => function () {
				return $this->shipmentMethod === DeliveryRussiaTc::getCategory();
			}],
			[['shipmentCity'], 'string', 'max' => 128],

			[['shipmentMethod'], 'validateShipment', 'skipOnError' => true],

			[['comment'], 'trim'],
			[['comment'], 'string', 'length' => [0, 200]],

		];
	}

	/**
	 * @return array|Good[]
	 */
	protected function getGoodModels(): array
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
	 */
	public function validateGoodsData($attribute, $params, $validator): void
	{
		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}
		Model::loadMultiple($this->getGoodModels(), $this->{$attribute}, '');
		if (!Model::validateMultiple($this->getGoodModels())) {
			$errors = [];
			/** @var Good $model */
			foreach ($this->getGoodModels() as $i => $model) {
				if ($model->hasErrors()) {
					$errors[(string)$i] = $model->getErrors();
				}
			}
			//todo неправильно сериализует, для ошибок по товарам
			// принудительно отдавать объект, а не массив
			// (воспроизвести можно по кол-ву 500)
			$this->addError('goods', (object)$errors);
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws ReflectionException
	 * @throws Throwable
	 */
	public function validateShipment($attribute, $params, $validator): void
	{

		if ($validator->skipOnError && $this->hasErrors())
			return;

		$address = null;
		if (!empty($this->shipmentAddressId)) {
			$address = $this->getAddressModel();
		}

		$inputData = [
			'deliveryType' => $this->shipmentMethod,
			'deliveryParams' => [
				'shopId' => $this->shipmentShopId,
				'city' => $address instanceof OptUserAddress ? ($address->address['city'] ?? null) : null,
				'address' => $address instanceof OptUserAddress ? ($address->address['street'] ?? null) . ' ' . ($address->address['house'] ?? null) : null,
				'coords' => $address instanceof OptUserAddress ? ($address->address['coords'] ?? null) : null,
				'date' => !empty($this->shipmentDate) ? DateTime::createFromFormat('Y-m-d', $this->shipmentDate)->format('Y-m-d\TH:i:s.u\Z') : null,
				// tc
				'userCity' => $this->shipmentCity,
				'tcId' => $this->shipmentTcId,
			],
			'customerType' => CustomerB2BClient::getType(),
			'customerParams' => [
				'payment' => $this->paymentMethod,
				'comment' => $this->comment,
			],
		];

		$form = $this->getEcommerceOrderForm();
		$form->load($inputData, '');

		if (false === $form->validate()) {

			$mapper = [

				'pickup_date' => 'shipmentDate',
				'pickup_shop_id' => 'shipmentShopId',

				'city_region_date' => 'shipmentDate',
				'city_region_city' => 'shipmentAddressId',
				'city_region_address' => 'shipmentAddressId',
				'city_region_coords' => 'shipmentAddressId',

				'russia_tc_tc_id' => 'shipmentTcId',
				'russia_tc_user_city' => 'shipmentCity',

				'b2b_client_payment' => 'paymentMethod',
				'b2b_client_comment' => 'comment',
			];

			$errors = [];
			foreach ($form->getErrors() as $field => $messages) {

				$field = $mapper[$field] ?? $field;
				$errors[$field] = array_merge($errors[$field] ?? [], $messages);
			}

			$this->addErrors($errors);
		}

	}

	/**
	 * @return OptUserAddress[]|array
	 * @throws Throwable
	 */
	protected function getAddressOptions(): array
	{

		static $cache = [];
		if (!isset($cache[$this->shipmentMethod])) {

			$cache[$this->shipmentMethod] = [];

			$reader = OptUserAddress::find()
				->byOptUserId($this->_user->getId())
				//->byDeliveryType($this->shipmentMethod)
				->byUseInApi();

			/** @var OptUserAddress $address */
			foreach ($reader->each() as $address) {

				$addressGeoPosition = is_array($address->address) && isset($address->address['coords']) && count($address->address['coords']) == 2
					? $address->address['coords'] : null;

				if ($addressGeoPosition === null) {
					continue;
				}

				$cache[$this->shipmentMethod][$address->id] = $address;
				$cache[$this->shipmentMethod][$address->hash] = $address;
			}

		}

		return $cache[$this->shipmentMethod];
	}

	/**
	 * @return OptUserAddress|null
	 * @throws Throwable
	 */
	protected function getAddressModel(): ?OptUserAddress
	{
		if ($this->_addressModel === null) {
			$this->_addressModel = $this->getAddressOptions()[$this->shipmentAddressId] ?? null;
		}
		return $this->_addressModel;
	}

	protected function getTcOptions(): array
	{
		$options = $this->_ecommerce->getDeliveryCityTcRepository()->getDeliveryTc();
		return ArrayHelper::index($options->getData(), 'id');
	}

	/**
	 * @return bool
	 * @throws Throwable
	 */
	public function placeOrder(): bool
	{

		if (false === $this->validate()) {
			return false;
		}

		$result = $this->getEcommerceOrderForm()->placeOrder(false);
		if (false === $result) {
			$this->addErrors($this->getEcommerceOrderForm()->getErrors());
		}

		return $result;
	}

	public function getReserve(): OrderReserve
	{
		return $this->getEcommerceOrderForm()->getReserve();
	}

}
