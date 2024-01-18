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
use Exception;
use myexample\ecommerce\deliveries\DeliveryCityRegion;
use myexample\ecommerce\deliveries\DeliveryInterface;
use myexample\ecommerce\deliveries\DeliveryManagerInterface;
use myexample\ecommerce\deliveries\DeliveryPickup;
use myexample\ecommerce\deliveries\DeliveryRussiaTc;
use myexample\ecommerce\EcommerceInterface;
use myexample\ecommerce\GeoPosition;
use myexample\ecommerce\GoodCollection;
use myexample\ecommerce\OrderB2BApiForm;
use myexample\ecommerce\UserInterface;
use Throwable;
use yii\base\Model;
use function count;
use function is_array;

/**
 * Class OrderForm
 * @package api\modules\regular\models\forms
 *
 * @property OrderB2BApiForm $ecommerceOrderForm
 * @property OptUserAddress|null $addressModel
 * @property array|OptUserAddress[] $addressOptions
 * @property DeliveryManagerInterface $deliveryManager
 * @property bool|array $deliveryInfo
 * @property Good[]|array $goodModels
 */
class OrderDeliveryForm extends Model
{

	public $goods;

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

	/**
	 * @var DeliveryManagerInterface
	 */
	protected $_deliveryManager;
	protected $_goods;

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

	protected function getDeliveryManager(): DeliveryManagerInterface
	{

		if (null === $this->_deliveryManager) {

			$goodCollection = new GoodCollection();

			/** @var Good $good */
			foreach ($this->getGoodModels() as $good) {

				$goodEntity = new GoodEntity(new GoodIdentity($good->sku), $good->quantity);
				$goodCollection->add($goodEntity);
			}

			$this->_deliveryManager = $this->_ecommerce->getDeliveryManager($goodCollection, $this->_region, $this->_user);
		}

		return $this->_deliveryManager;
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
			/** @var Good $model */
			foreach ($this->getGoodModels() as $i => $model) {

				if ($model->hasErrors()) {
					$this->addError('goods', $model->getErrors());
				}
			}
		}
	}

	/**
	 * @return OptUserAddress[]|array
	 * @throws Throwable
	 */
	protected function getAddressOptions(): array
	{

		static $cache;
		if (!isset($cache)) {

			$cache = [];

			$reader = OptUserAddress::find()
				->byOptUserId($this->_user->getId())
				->byUseInApi();

			/** @var OptUserAddress $address */
			foreach ($reader->each() as $address) {

				$addressGeoPosition = is_array($address->address) && isset($address->address['coords']) && count($address->address['coords']) == 2
					? $address->address['coords'] : null;

				if ($addressGeoPosition === null) {
					continue;
				}

				$cache[$address->hash] = $address;
			}

		}

		return $cache;
	}

	/**
	 * @return bool|array
	 * @throws Exception
	 * @throws Throwable
	 */
	public function getDeliveryInfo()
	{

		if (!$this->validate()) {
			return false;
		}

		$result = [];

		/** @var DeliveryInterface|DeliveryPickup|DeliveryCityRegion $delivery */
		foreach ($this->getDeliveryManager()->getActiveDeliveries() as $delivery) {

			$data = null;
			switch ($delivery::getCategory()) {

				case DeliveryPickup::getCategory():

					$data = $delivery->getDataForOrder();

					$data['shops'] = [];
					foreach ($data['items'] as $shop) {
						$data['shops'][] = [
							'shipmentShopId' => $shop['shopId'],
							'dates' => [
								'min' => (new DateTime($shop['deliveryDate']['min']['dayDatetime']))->format('Y-m-d'),
								'max' => (new DateTime($shop['deliveryDate']['max']['dayDatetime']))->format('Y-m-d'),
							],
						];
					}

					unset($data['items'], $data['active']);

					$data['payments'] = reset($data['payments']);
					$data['payments'] = array_values(array_intersect_key(
						$data['payments'],
						$this->_ecommerce->getPaymentTypes(),
						array_flip($this->_user->getPaymentTypes())
					));

					break;

				case DeliveryCityRegion::getCategory():

					$data = [
						'addresses' => [],
					];

					foreach ($this->getAddressOptions() as $address) {

						$geoPosition = new GeoPosition($address['address']['coords']['lat'], $address['address']['coords']['lng']);
						$dates = $delivery->getDeliveryDaysDataByGeoPosition($geoPosition);
						if ($dates !== null) {
							$dates = [
								'min' => (new DateTime($dates['deliveryDate']['min']['dayDatetime']))->format('Y-m-d'),
								'max' => (new DateTime($dates['deliveryDate']['max']['dayDatetime']))->format('Y-m-d'),
								'schedule' => $dates['deliveryDate']['schedule'] ?? [],
							];
						}

						$addressData = [
							'shipmentAddressId' => $address->hash,
							'dates' => $dates,
						];

						$data['addresses'][] = $addressData;
					}

					$data['payments'] = array_merge(...array_values($delivery->getDataForOrder()['payments']));
					$data['payments'] = array_values(array_intersect_key(
						$data['payments'],
						$this->_ecommerce->getPaymentTypes(),
						array_flip($this->_user->getPaymentTypes())
					));

					break;


				case DeliveryRussiaTc::getCategory():

					$data = [
						'tc' => $delivery->getDataForOrder()['tc'] ?? [],
					];

					$data['payments'] = array_merge(...array_values($delivery->getDataForOrder()['payments']));
					$data['payments'] = array_values(array_intersect_key(
						$data['payments'],
						$this->_ecommerce->getPaymentTypes(),
						array_flip($this->_user->getPaymentTypes())
					));

					break;
			}

			$result[$delivery::getCategory()] = $data;
		}

		return $result;
	}

}
