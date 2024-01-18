<?php

namespace common\components;

use common\interfaces\GoodInterface;
use common\interfaces\RegionEntityInterface;
use common\models\OrderType;
use common\models\ShoppingCartGood;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\interfaces\GoodEntityInterface;
use domain\services\GoodAvailabilityService;
use yii\base\Component;
use yii\base\InvalidConfigException;
use function get_class;

class Delivery extends Component
{

	/**
	 * @var array доступные типы доставок
	 */
	public $deliveryTypes;

	/**
	 * @var integer id тип доставки для ПЭК
	 * Это свойство должно быть установлено при конфигурации приложения
	 */
	public $deliveryIdPek;

	/**
	 * @var GoodAvailabilityService
	 */
	protected $availabilityService;

	public function __construct(GoodAvailabilityServiceInterface $availabilityService, array $config = [])
	{
		$this->availabilityService = $availabilityService;
		parent::__construct($config);
	}

	/**
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		parent::init();
		if ($this->deliveryTypes === null) {
			$this->deliveryTypes = [];
		}
		if ((int)$this->deliveryIdPek === 0) {
			throw new InvalidConfigException(get_class($this) . '::$deliveryIdPek must be set.');
		}
	}

	/**
	 * @return GoodAvailabilityService
	 */
	public function getAvailabilityService(): GoodAvailabilityService
	{
		return $this->availabilityService;
	}

	public function getDeliveryTcOptions()
	{
		return [
			'pak' => 'ПЭК',
			'buy' => 'Байкал-Сервис',
			'del' => 'Деловые линии',
			'oby' => 'Объем',
			'eks' => 'Экспресс-Авто',
			'vst' => 'ВСТ',
			'kit' => 'КИТ',
			'jde' => 'ЖелДорЭкспедиция',
			'nrg' => 'Энергия',
			'anc' => 'Анкор',
			'voz' => 'Возовоз',
		];
	}

	public function getPickupStoresForShoppingCartItems(array $items, RegionEntityInterface $region, $orderTypeId = OrderType::ORDER_TYPE_PICKUP)
	{
		$goods = [];
		foreach ($items as $itm) {
			/**
			 * @var GoodEntityInterface $good
			 */
			$good = $itm->getGood();
			// Если товар не доступен
			if (null === $good) {
				continue;
			}
			$goods[] = [
				'id' => $good->getId(),
				'quantity' => $itm->getItem()->quantity,
			];
		}
		return $this->getPickupStoresForGoods($goods, $region->getZoneId(), $orderTypeId);
	}

	public function getPickupStoresForGoods(array $goods, int $zoneId, $orderTypeId = OrderType::ORDER_TYPE_PICKUP)
	{
		/**
		 * @var ShoppingCartGood $good
		 */
		$stores = [];
		$storesCurrent = [];
		foreach ($goods as $good) {
			$avAll = $this->availabilityService->getRealAvailabilityOrderByGoodIdAndZoneId($good['id'], $zoneId);
			$skipShopId = null;
			$prevShopId = null;
			if (!isset($avAll[$orderTypeId]['availability'])) {
				$storesCurrent = [];
				continue;
			}
			$avArr = $avAll[$orderTypeId]['availability'];
			$totalAmount = 0;
			$days = 0;
			foreach ($avArr as $av) {
				$shopId = (int)$av['shop_id'];
				if ($shopId === $skipShopId) {
					continue;
				}
				$currentAmount = $av['amount'];
				$currentDays = $av['days'];

				if ($prevShopId == $shopId) {
					$totalAmount += $currentAmount;
				} else {
					$totalAmount = $currentAmount;
				}
				$days = $currentDays;
				if ($good['quantity'] <= $totalAmount) {
					$storesCurrent[$shopId] = [
						'shop_id' => $shopId,
						'amount' => $totalAmount,
						'days' => $days,
					];
					$skipShopId = $shopId;
				}
				$prevShopId = $shopId;
			}
			if ([] === $stores) {
				$stores = $storesCurrent;
			} else {
				$stores = array_intersect_key($stores, $storesCurrent);
				foreach ($stores as $storeId => $store_data) {
					$secondStoreData = $storesCurrent[$storeId];
					if ($secondStoreData['days'] > $store_data['days']) {
						$stores[$storeId]['days'] = $secondStoreData['days'];
					}
				}
			}
			$storesCurrent = [];
		}
		return $stores;
	}

	/**
	 * Возвращает список магазинов/складов для текущей корзины с которых будет осуществлятся
	 * формирование корзины заказа
	 * @param array $items
	 * @param RegionEntityInterface $region
	 * @param $orderTypeId
	 * @param int $toShopId
	 * @return array
	 */
	public function getStoresForShoppingCartItems(array $items, RegionEntityInterface $region, $orderTypeId, int $toShopId)
	{
		$goods = [];
		/** @var ShoppingCartItem $itm */
		foreach ($items as $itm) {
			/**
			 * @var GoodEntityInterface $good
			 */
			$good = $itm->getGood();
			// Если товар не доступен
			if (null === $good) {
				continue;
			}
			$goods[] = [
				'id' => $good->getId(),
				'quantity' => $itm->getItem()->quantity,
			];
		}
		return $this->getStoresForGoods($goods, $region->getZoneId(), $orderTypeId, $toShopId);
	}

	/**
	 * Возвращает список магазинов/складов с которых будет осуществлятся формирование корзины заказа
	 * @param array $goods [[id, quantity],..]
	 * @param int $zoneId
	 * @param $orderTypeId
	 * @param int $toShopId
	 * @return array
	 */
	public function getStoresForGoods(array $goods, int $zoneId, int $orderTypeId, int $toShopId)
	{
		$stores = [];
		$error = false;
		/**
		 * @var ShoppingCartItem $good
		 */
		foreach ($goods as $good) {
			// Сколько требуется товара
			$qty = $good['quantity'];
			// берем доступность для данного товара
			$avAll = $this->availabilityService->getRealAvailabilityOrderByGoodIdAndZoneId($good['id'], $zoneId);
			// Если в выбранном типе доставки нет наличия,
			// значит что-то поменялось, нужно уведомить пользователя
			if (!isset($avAll[$orderTypeId]['availability']) || [] === $avAll[$orderTypeId]['availability']) {
				$error = true;
				break;
			}
			$avArr = $avAll[$orderTypeId]['availability'];
			foreach ($avArr as $av) {
				$shopId = (int)$av['shop_id'];
				$amount = $av['amount'];
				if ($shopId === $toShopId) {
					// Если товара в магазине достаточно
					if ($amount >= $qty) {
						$stores[] = [
							'item_id' => $good['id'],
							'shop_id' => $av['from_shop_id'],
							'qty' => $qty,
						];
						$qty = 0; // we've fullfilled this position
						break;
					}
					$stores[] = [
						'item_id' => $good['id'],
						'shop_id' => $av['from_shop_id'],
						'qty' => (int)$amount,
					];
					// Уменьшаем кол-во требуемого товара,
					// на кол-во товара в наличи на текущем магазине/складе
					$qty -= (int)$amount;
				}
			}
			// Если возник недобор, значит изменилось кол-во,
			// уведомляем пользователя
			if ($qty > 0) {
				$error = true;
				break;
			}
		}
		return $error ? [] : $stores;
	}

	/**
	 * Возвращает возможные типы доставки и кол-во дней необходимых для типа доставки всего заказа
	 * @param array $items состав заказа
	 * @param RegionEntityInterface $region для региона
	 * @return array массив orderType => days
	 */
	public function getOrderTypesForShoppingCartItems(array $items, RegionEntityInterface $region)
	{
		$goods = [];
		foreach ($items as $itm) {
			/**
			 * @var GoodInterface $good
			 */
			$good = $itm->getGood();
			if (null === $good) {
				continue;
			}
			$goods[] = [
				'id' => $good->getId(),
				'quantity' => $itm->getItem()->quantity,
			];
		}
		return $this->getOrderTypesForGoods($goods, $region->getZoneId());
	}

	/**
	 * Возвращает возможные типы доставки и кол-во дней необходимых для типа доставки всего заказа
	 * @param array $goods состав заказа
	 * @param int $zoneId
	 * @return array массив orderType => days
	 */
	public function getOrderTypesForGoods(array $goods, int $zoneId)
	{
		$avArr = [];
		$count = 0;
		foreach ($goods as $itm) {
			$avArr[$itm['id']] = $this->availabilityService
				->getRealAvailability($itm['id'], $zoneId);
			$count++;
		}
		$orderTypes = $this->availabilityService->getOrderTypes();
		/**
		 * @var \domain\entities\order\OrderType $orderType
		 */
		$orderTypesResult = [];
		foreach ($orderTypes as $orderType) {
			$otId = $orderType->getId();
			$c = $count;
			$minDays = -1;
			foreach ($goods as $itm) {
				$goodId = $itm['id'];
				$amount = $itm['quantity'];
				if (isset($avArr[$goodId][$otId])) {
					$totalAmount = 0;
					$currentDays = 0;
					foreach ($avArr[$goodId][$otId]['availability'] as $av) {
						$totalAmount += $av['qty'];
						if ($amount <= $totalAmount) {
							$currentDays = $avArr[$goodId][$otId]['days'] + $av['days'];
							break;
						}
					}
					if ($minDays <= $currentDays) {
						$minDays = $currentDays;
					}
					if ($amount <= $totalAmount) {
						$c--;
					}
				}
			}
			if ($c === 0) {
				$orderTypesResult[$otId] = $minDays;
			}
		}
		return $orderTypesResult;
	}

}
