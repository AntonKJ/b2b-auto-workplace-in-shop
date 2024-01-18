<?php

namespace common\components\deliveries;

use common\components\deliveries\forms\DeliveryCityRegionForm;
use common\components\ExpressDelivery;
use common\components\payments\PaymentCash;
use common\components\payments\PaymentInvoice;
use common\interfaces\PoiInterface;
use common\models\DeliveryCity;
use common\models\DeliveryZone;
use common\models\DeliveryZoneDeliveryCity;
use common\models\MetroStation;
use common\models\OptUser;
use common\models\OptUserAddress;
use common\models\OrderType;
use common\models\OrderTypeGroup;
use common\models\query\DeliveryZoneQuery;
use common\models\query\OrderTypeQuery;
use common\models\Shop;
use DateTime;
use domain\entities\GeoPosition;
use Exception;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use function count;
use function is_array;

class DeliveryCityRegion extends DeliveryAbstract
{

	public const MAX_RESERVE_DAYS = 4;

	// Отображение периода на календаре +дней
	public const CALENDAR_RANGE_DAYS = 5;

	public const MAX_ALLOWED_ADDRESSES = 10;

	/**
	 * @var integer
	 */
	public $deliveryMinimumDays;

	/**
	 * @var DateTime
	 */
	public $dateFrom;

	/**
	 * @throws Exception
	 */
	public function init()
	{
		$this->deliveryMinimumDays = 0;
		$this->dateFrom = new DateTime();

		parent::init();
	}

	/**
	 * Загружаем данные для города и области
	 * @return array
	 * @throws Throwable
	 */
	public function getData()
	{

		static $data;

		Yii::beginProfile(__METHOD__, 'delivery');
		if (null === $data) {

			/** @var OptUser $user */
			$user = $this->getUser();
			$region = $this->getRegion();

			/**
			 * @var int[] $orderTypeGroup
			 */
			$orderTypeGroup = [$region->getOrderTypeGroupId()];

			if ($user !== null && $user->category !== null)
				$orderTypeGroup[] = $user->getOrderTypeGroupId();

			// Считаем пересечение типов заказов в группах
			$otIds = OrderTypeGroup::calculateOrderTypeGroupIntersect($orderTypeGroup);

			// Получаем все типы доставок по категориям, группе региона и вычисленным типам
			$query = OrderType::find()
				->alias('ot')
				->byCategory([
					OrderType::CATEGORY_REGION,
					OrderType::CATEGORY_CITY,
				])
				->byOrderTypeGroup($region)
				->byId($otIds)
				->orderByPriority()
				->joinWith([
					// Также выбираем зоны в которых есть хотя-бы один город
					'deliveryZones' => function (DeliveryZoneQuery $q) {
						$q
							->alias('dz')
							->leftJoin([
								'dzdc' => DeliveryZoneDeliveryCity::find()
									->select(['delivery_zone_id', 'COUNT(delivery_city_id) cities_cnt'])
									->groupBy('delivery_zone_id'),
							], 'dzdc.delivery_zone_id = dz.id');
					},
				], false)
				->andWhere('dzdc.cities_cnt > 0 OR ot.category = :otCategory', [
					':otCategory' => OrderType::CATEGORY_CITY,
				])
				->with([
					'deliveryZones' => static function (DeliveryZoneQuery $q) {
						$q
							->alias('dz')
							->addSelectAreaAsJson()
							->leftJoin([
								'dzdc' => DeliveryZoneDeliveryCity::find()
									->select(['delivery_zone_id', 'COUNT(delivery_city_id) cities_cnt'])
									->groupBy('delivery_zone_id'),
							], 'dzdc.delivery_zone_id = dz.id')
							->joinWith([
								'orderType' => static function (OrderTypeQuery $q) {
									$q
										->alias('ot');
								},
							], false)
							->andWhere('dzdc.cities_cnt > 0 OR ot.category = :otCategory', [
								':otCategory' => OrderType::CATEGORY_CITY,
							]);
					},
				]);

			$orderTypeByShops = [];
			foreach ($query->each() as $ot) {
				$orderTypeByShops[(int)$ot->from_shop_id][(int)$ot->getId()] = $ot;
			}

			// Берем ID активных магазинов
			$regionShopIds = Shop::find()
				->select(['shop_id'])
				->active()
				->column();

			$regionShopIds = array_fill_keys($regionShopIds, null);

			// вычисляем пересечения по магазинам региона и магазинам типов доставок
			$orderTypeByShops = array_intersect_key($orderTypeByShops, $regionShopIds);

			$orderTypesCollection = [];
			$shopsByOrderType = [];

			foreach ($orderTypeByShops as $shopId => $ots) {
				foreach ($ots as $otId => $ot) {
					$shopsByOrderType[$otId][] = $shopId;
					if (!isset($orderTypesCollection[$otId])) {
						$orderTypesCollection[$otId] = $ot;
					}
				}
			}

			// Получаем доступные типы доставок и кол-во дней для корзины
			$activeOrderTypes = $this->getDeliveryComponent()
				->getOrderTypesForGoods($this->goods->getData(), $region->getZoneId());

			// Фильтруем по доступным типам доставок
			$shopsByOrderType = array_intersect_key($shopsByOrderType, $activeOrderTypes);

			$data = [];
			$deliveryZones = [];

			$active = false;

			$today = new DateTime();

			foreach ($shopsByOrderType as $otId => $shopsIds) {

				// Получаем список магазинов с наличием и днями когда можно забрать товар
				$shopsWithGoods = $this->getDeliveryComponent()
					->getPickupStoresForGoods($this->goods->getData(), $region->getZoneId(), $otId);

				// Фильтруем магазины, где есть товар
				$shopsWithGoods = array_intersect_key($shopsWithGoods, array_fill_keys($shopsIds, null));

				// Если нет магазинов с товаром для текущей
				// зоны доставки, переходим дальше
				if ([] === $shopsWithGoods) {
					continue;
				}

				$active = true;

				/**
				 * @var OrderType $deliveryZone
				 */
				$deliveryZone = $orderTypesCollection[$otId];

				$minDays = $region->getClosestDeliveryDay($activeOrderTypes[$otId]);

				$deliveryZone = $deliveryZone->toArray([], ['deliveryZones', 'deliverySchedule']);

				$deliveryZone['deliveryDate'] = $this->calculateDeliveryData($minDays);

				if (isset($deliveryZone['deliverySchedule'])
					&& [] !== $deliveryZone['deliverySchedule']) {
					$deliveryZone['scheduleDays'] = ExpressDelivery::resolveDaysSchedules(
						$region,
						$deliveryZone['category'],
						$deliveryZone['deliveryDate']['min']['dayDatetime'],
						$deliveryZone['deliverySchedule']
					);
					if (is_array($deliveryZone['scheduleDays'])) {
						$deliveryZone['scheduleDays'] = array_map('array_values', $deliveryZone['scheduleDays']);
					}
				}
				ExpressDelivery::addExpressDeliveryInfo($deliveryZone, $region);

				$deliveryZones[$deliveryZone['id']] = $deliveryZone;
			}

			// Вычисляем пересечение активных
			$orderTypesCollection = array_intersect_key($orderTypesCollection, $deliveryZones);

			$deliveryZoneIds = [];
			foreach ($orderTypesCollection as $orderTypeItm) {

				if ([] === $orderTypeItm->deliveryZones) {
					continue;
				}

				foreach ($orderTypeItm->deliveryZones as $dZone) {
					$deliveryZoneIds[] = $dZone->id;
				}
			}

			// Города ----------------------------------------------------------
			$cityListQuery = DeliveryCity::find()
				->byDeliveryZoneId($deliveryZoneIds)
				->with(['zones'])
				->defaultOrder()
				->all();

			$cityList = [];
			/** @var DeliveryCity $city */
			foreach ($cityListQuery as $city) {

				if (!isset($cityList[$city->getId()])) {

					$cityList[$city->getId()] = $city->toArray();
					$cityList[$city->getId()]['fromZones'] = [];
				}

				/** @var DeliveryZone $zone */
				foreach ($city->zones as $zone) {

					if (!isset($activeOrderTypes[$zone->order_type_id])) {
						continue;
					}

					if (isset($cityList[$city->getId()]['fromZones'][$zone->order_type_id])) {
						$cityList[$city->getId()]['fromZones'][$zone->order_type_id]['deliveryZones'][] = $zone->id;
						continue;
					}

					$deliveryDate = $this->calculateDeliveryData($city->getClosestDeliveryDay($activeOrderTypes[$zone->order_type_id]));
					$cityList[$city->getId()]['fromZones'][$zone->order_type_id] = array_merge([
						'orderTypeId' => $zone->order_type_id,
						'deliveryZones' => [$zone->id],
					], $deliveryDate);
				}
			}

			// Метро ----------------------------------------------------------
			$metroListQuery = MetroStation::find()
				->byOrderTypeId(array_keys($orderTypesCollection))
				->with('orderTypeRel')
				->defaultOrder()
				->all();

			$metroList = [];
			/** @var MetroStation $metroStation */
			foreach ($metroListQuery as $metroStation) {

				if (!isset($metroList[$metroStation->id])) {

					$metroList[$metroStation->id] = $metroStation->toArray();
					$metroList[$metroStation->id]['fromZones'] = [];
				}

				$deliveryDate = $this->calculateDeliveryData($metroStation->getClosestDeliveryDay($activeOrderTypes[$metroStation->orderTypeRel->order_type_id]));
				$metroList[$metroStation->id]['fromZones'][$metroStation->orderTypeRel->order_type_id] = array_merge([
					'orderTypeId' => $metroStation->orderTypeRel->order_type_id,
				], $deliveryDate);
			}

			if ($active === false) {
				Yii::info("{$this->getTitle()} недоступна, т.к. нет доступных магазинов с товаром.");
				if (isset($shopsByOrderType)) {
					Yii::info($shopsByOrderType);
				}
			}

			$payments = $this->getPayments();
			if ($payments === []) {
				Yii::info("{$this->getTitle()} недоступна, т.к. нет доступных способов оплаты для пользователя.");
				$active = false;
			}

			$data = [
				'active' => $active,
				'zones' => $deliveryZones,
				'poi' => [
					DeliveryCity::POI_TYPE => $cityList,
					MetroStation::POI_TYPE => $metroList,
				],
				'payments' => $payments,
			];

		}
		Yii::endProfile(__METHOD__, 'delivery');

		return $data;
	}

	/**
	 * @return bool
	 * @throws Throwable
	 */
	public function isActive(): bool
	{

		$active = true;

		if ($active) {

			$data = $this->getData();
			$active = $active && (isset($data['active']) && $data['active']);
		}

		return $active;
	}

	public function getTitle(): string
	{
		return 'Доставка курьером';
	}

	static public function getCategory(): string
	{
		return implode('_', [OrderType::CATEGORY_CITY, OrderType::CATEGORY_REGION]);
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	public function getPayments()
	{

		static $types;

		if ($types === null) {

			$types = [
				PaymentCash::getCode() => new PaymentCash(),
				PaymentInvoice::getCode() => new PaymentInvoice(),
			];

			/** @var OptUser $user */
			$user = $this->getUser();
			if ($user !== null && $user->category !== null) {

				$allowedTypes = $user->getPaymentTypes();
				$allowedTypes = array_flip($allowedTypes);

				$types = array_intersect_key($types, $allowedTypes);
				$types = array_values($types);
			}
		}

		return $types;
	}

	public function getFormModel()
	{
		return new DeliveryCityRegionForm($this);
	}

	/**
	 * @throws Throwable
	 */
	public function getDataForClient()
	{
		$data = $this->getData();

		$data['zones'] = array_values($data['zones']);

		foreach ($data['poi'] as $key => $val) {

			$data['poi'][$key] = array_map(function ($v) {

				$v['fromZones'] = array_values($v['fromZones']);
				return $v;
			}, array_values($val));

		}

		if ($this->getFormModel()->isAllowedAddressStore()) {

			/** @var OptUser $user */
			$user = $this->getUser();

			if ($user !== null) {

				$addressesQuery = $user
					->getAddresses()
					->byDeliveryType(static::getCategory())
					->orderDefault()
					->limit(static::MAX_ALLOWED_ADDRESSES);

				/** @var OptUserAddress $address */
				foreach ($addressesQuery->each() as $address) {

					$addressGeoPosition = is_array($address->address) && isset($address->address['coords']) && count($address->address['coords']) == 2
						? $address->address['coords'] : null;

					$address = $address->toArray();
					if ($addressGeoPosition !== null) {

						$geoPosition = new GeoPosition($addressGeoPosition['lat'], $addressGeoPosition['lng']);
						$address['deliveryDate'] = $this->getDeliveryDaysDataByGeoPosition($geoPosition);
					}

					$data['addresses'][] = $address;
				}
			}
		}

		return $data;
	}

	/**
	 * @param GeoPosition $geoPosition
	 * @throws Throwable
	 */
	public function getDeliveryDaysDataByGeoPosition(GeoPosition $geoPosition)
	{

		$ot = $this->getOrderTypeByGeoPosition($geoPosition);
		$poi = $this->getClosestPoiByGeoPosition($geoPosition);

		if (null === $ot || null === $poi)
			return null;

		$deliveryRangeData = $this->getData()['zones'][$ot->getId()]['deliveryDate'];

		if (isset($this->getData()['poi'][$poi->getPoiType()][$poi->id]['fromZones'][$ot->getId()])) {

			$deliveryRangeData = $this->getData()['poi'][$poi->getPoiType()][$poi->id]['fromZones'][$ot->getId()];
			$deliveryRangeData['days'] = $poi->getDeliveryDays();
		}

		return $deliveryRangeData;
	}

	/**
	 * Возвращает активные зоны для текущей гео-точки
	 * @param GeoPosition $geoPosition
	 * @param bool $refresh
	 * @return mixed
	 * @throws Throwable
	 */
	public function getOrderTypeIdsByGeoPosition(GeoPosition $geoPosition, bool $refresh = false)
	{

		$key = md5((string)$geoPosition);

		static $orderTypeIds = [];

		if (!isset($orderTypeIds[$key]) || $refresh) {

			$zoneIds = array_keys($this->getData()['zones'] ?? []);

			// Получаем все типы доставок в которых есть зоны с текущей точкой
			$orderTypeIds[$key] = DeliveryZone::find()
				->select(['order_type_id', 'category' => 'ot.category'])
				->innerJoinWith([
					'orderType' => function (OrderTypeQuery $q) {
						$q
							->alias('ot')
							->byCategory([
								OrderType::CATEGORY_REGION,
								OrderType::CATEGORY_CITY,
							]);
					},
				], false)
				->joinWith([
					'citiesRel' => function (ActiveQuery $q) {
						$q->alias('dcr');
					},
				], false)
				->byOrderTypeId($zoneIds)
				->byGeoPosition($geoPosition)
				->groupBy('order_type_id')
				->andHaving('COUNT(dcr.delivery_city_id) > 0 OR category = :categoryCity', [':categoryCity' => OrderType::CATEGORY_CITY])
				->column();
		}

		return $orderTypeIds[$key];
	}


	/**
	 * @param GeoPosition $geoPosition
	 * @param bool $refresh
	 * @return array|DeliveryCity|MetroStation|null
	 * @throws Throwable
	 */
	public function getClosestPoiByGeoPosition(GeoPosition $geoPosition, bool $refresh = false)
	{

		$key = md5((string)$geoPosition);

		static $closestPoi = [];

		if (!isset($closestPoi[$key]) || $refresh) {

			// Получаем все типы доставок в которых есть зоны с текущей точкой
			$orderTypeIds = $this->getOrderTypeIdsByGeoPosition($geoPosition, $refresh);

			$pointNotInZones = $orderTypeIds === [];

			// Если нет зон с текущей точкой, ставим все доступные зоны для текущей доставки
			if ($pointNotInZones)
				$orderTypeIds = array_keys($this->getData()['zones'] ?? []);

			$closestPoiQuery = DeliveryCity::find()
				->byOrderTypeId($orderTypeIds)
				->orderByClosest($geoPosition)
				->limit(1);

			if ($pointNotInZones)
				$closestPoiQuery->byDistanceEqLess($geoPosition, DeliveryCity::AREA_RADIUS);

			$closestPoi[$key] = $closestPoiQuery->one();

			$closestMetroQuery = MetroStation::find()
				->byOrderTypeId($orderTypeIds)
				->orderByClosest($geoPosition)
				->limit(1);

			if ($pointNotInZones)
				$closestMetroQuery->byDistanceEqLess($geoPosition, MetroStation::AREA_RADIUS);

			$closestMetro = $closestMetroQuery->one();

			if ($closestPoi[$key] === null || ($closestMetro !== null && $closestPoi[$key]->getDistance() > $closestMetro->getDistance()))
				$closestPoi[$key] = $closestMetro;
		}

		return $closestPoi[$key];
	}

	/**
	 * @param GeoPosition $geoPosition
	 * @param bool $refresh
	 * @return mixed
	 * @throws Throwable
	 */
	public function getOrderTypeByGeoPosition(GeoPosition $geoPosition, bool $refresh = false)
	{

		$key = md5((string)$geoPosition);

		static $orderType = [];

		if (!isset($orderType[$key]) || $refresh) {

			$orderType[$key] = null;
			$poi = $this->getClosestPoiByGeoPosition($geoPosition, $refresh);

			if ($poi instanceof PoiInterface) {

				$orderTypeIds = $this->getOrderTypeIdsByGeoPosition($geoPosition, $refresh);

				$pointNotInZones = $orderTypeIds === [];

				// Если нет зон с текущей точкой, ставим все доступные зоны для текущей доставки
				if ($pointNotInZones)
					$orderTypeIds = array_keys($this->getData()['zones'] ?? []);

				$orderType[$key] = $poi->getOrderTypeQuery()
					->byId($orderTypeIds)
					->orderByPriority()
					->limit(1)
					->one();
			}
		}

		return $orderType[$key];
	}

	/**
	 * @param $minDays
	 * @return array
	 * @throws Exception
	 */
	protected function calculateDeliveryData($minDays)
	{

		$maxDays = $minDays + static::CALENDAR_RANGE_DAYS;

		$today = new DateTime();

		$minDt = (clone $today)->setTime(0, 0);
		if ($minDays > 0)
			$minDt->modify("+{$minDays} days");

		$maxDt = (clone $today)->setTime(23, 59, 59);
		if ($maxDays > 0)
			$maxDt->modify("+{$maxDays} days");

		return [
			'min' => [
				'day' => $minDays,
				'dayDatetime' => $minDt->format('r'),
				'dayText' => $minDt->format('d.m.Y'),
			],
			'max' => [
				'day' => $maxDays,
				'dayDatetime' => $maxDt->format('r'),
				'dayText' => $maxDt->format('d.m.Y'),
			],
		];
	}

}
