<?php

namespace api\modules\vendor\modules\nokian\models\forms;

use api\modules\vendor\modules\nokian\components\Order;
use common\components\Delivery;
use common\components\payments\PaymentCash;
use common\interfaces\RegionEntityInterface;
use common\models\Shop;
use common\models\TyreGood;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use domain\services\Service1c;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use function is_array;

/**
 * Class OrderForm
 * @package api\modules\vendor\modules\nokian\models\forms
 *
 * @property Customer $customer
 */
class OrderAvailableForm extends Model
{

	public $entity;
	public $action;

	public $shopId;

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

	protected $_products;

	public function __construct(Order $component, GoodAvailabilityServiceInterface $availability, Delivery $delivery, array $config = [])
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

			[['entity'], 'compare', 'compareValue' => 'STORE'],
			[['action'], 'compare', 'compareValue' => 'CHECK'],

			[['shopId'], 'required'],
			[['shopId'], 'string', 'length' => [0, 255]],
			[['shopId'], 'validateShop', 'skipOnError' => true],

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

		];
	}

	public function getProductsModel()
	{
		if ($this->_products === null) {
			$this->_products = [];
			foreach (array_keys($this->product) as $i) {
				$product = new ProductAvailability();
				$this->_products[$i] = $product;
			}
		}
		return $this->_products;
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws InvalidConfigException
	 */
	public function validateProductsData($attribute, $params, $validator): void
	{
		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}
		Model::loadMultiple($this->productsModel, $this->{$attribute}, '');
		Model::validateMultiple($this->productsModel);
		if (!Model::validateMultiple($this->productsModel)) {
			/** @var Model $model */
			foreach ($this->productsModel as $i => $model) {
				foreach ($model->getErrors() as $field => $messages) {
					foreach ($messages as $message) {
						$this->addError('product', $message);
					}
				}
			}
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 */
	protected function updateAvailability(): void
	{
		/**
		 * @var Service1c $service1c
		 */
		$service1c = Yii::$container->get(Service1c::class);
		$goods = $this->getGoodsData();
		$zoneId = $this->getRegion()->getZoneId();
		// Получаем остатки для товаров в корзине
		$stocks = $service1c->getCurrentBalances(array_keys($goods));
		if (is_array($stocks)) {
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
	}

	/**
	 * @return bool|array
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 * @throws Exception
	 */
	public function getAvailability()
	{
		if (false === $this->validate()) {
			return false;
		}
		$this->updateAvailability();
		/**
		 * @var Service1c $service1c
		 */
		$goods = $this->getGoodsData();
		$zoneId = $this->getRegion()->getZoneId();

		$shopId = $this->getMyexampleShopId();

		$availability = [];
		foreach ($goods as $goodData) {

			$goodId = $goodData['id'];
			$goodCode = $goodData['code'];

			$availability[$goodId] = [
				'id' => $goodId,
				'code' => $goodCode,
				'quantity' => [0, 0, 0],
			];
			$data = $this->_availabilityComponent->getRealAvailabilityOrderByGoodIdAndZoneId($goodId, $zoneId);
			foreach ($data as $otId => $otData) {
				foreach (($otData['availability'] ?? []) as $avData) {
					if ($avData['shop_id'] != $shopId) {
						continue;
					}
					$type = 0;
					switch (true) {
						case (int)$avData['shop_id'] === (int)$avData['from_shop_id']:
							$type = 0;
							break;
						case (int)$avData['shop_id'] !== (int)$avData['from_shop_id']:
							if ((int)$avData['from_shop_id'] < 10000) {
								$type = 1;
							} else {
								$type = 2;
							}
							break;
					}
					$availability[$goodId]['quantity'][$type] += $avData['amount'];
				}
			}
			$availability[$goodId]['quantity'] = [$availability[$goodId]['quantity'][0] ?? 0];
		}
		return $availability;
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
			foreach ($reader->each() as $row) {
				$data[$key][$row['code']] = $row['id'];
			}
		}
		return $data[$key];
	}

	public function getGoodsData()
	{
		$goodsCodes = ArrayHelper::index($this->productsModel, static function ($v) {
			return $v->code;
		});
		static $data;
		$key = md5(implode(',', array_keys($goodsCodes)));
		if (!isset($data[$key])) {
			$goodIds = $this->getGoodsByCodes(array_keys($goodsCodes));
			$data[$key] = [];
			foreach ($goodIds as $goodCode => $goodId) {
				if (!isset($goodsCodes[$goodCode])) {
					continue;
				}
				$data[$key][$goodId] = [
					'id' => $goodId,
					'code' => $goodCode,
				];
			}
		}
		return $data[$key];
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 */
	public function validateShop($attribute, $params, $validator): void
	{
		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}
		$shopId = $this->getMyexampleShopId();
		if ($shopId === null) {
			$this->addError((string)$attribute, 'Магазин не найден');
		}
	}

	/**
	 * @return int|null
	 */
	public function getMyexampleShopId(): ?int
	{
		return $this->_orderComponent->getShopIdByVendorShopId($this->shopId);
	}

	/**
	 * @return Shop|null
	 */
	public function getShop(): ?Shop
	{
		static $shop;
		if ($shop === null) {
			$shop = Shop::find()->byId($this->getMyexampleShopId())->one();
			if ($shop === null) {
				$shop = false;
			}
		}
		return $shop === false ? null : $shop;
	}

	/**
	 * @return RegionEntityInterface
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
