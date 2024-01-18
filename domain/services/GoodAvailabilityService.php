<?php

namespace domain\services;

use common\dto\ShopAndGroup;
use common\interfaces\RegionEntityInterface;
use common\models\query\ShopNetworkQuery;
use common\models\Shop;
use DateTime;
use domain\entities\order\OrderType;
use domain\entities\shop\ShopGroupMove;
use domain\entities\shop\ShopStock;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\interfaces\GoodEntityInterface;
use domain\interfaces\OrderTypeRepositoryInterface;
use domain\interfaces\ShopGroupMoveRepositoryInterface;
use domain\interfaces\ShopNetworkRepositoryInterface;
use domain\interfaces\ShopRepositoryInterface;
use domain\interfaces\ShopStockRepositoryInterface;
use Exception;
use Yii;
use yii\caching\CacheInterface;
use yii\helpers\ArrayHelper;
use function func_get_args;
use function in_array;
use function is_string;

class GoodAvailabilityService implements GoodAvailabilityServiceInterface
{

	public const VIEW_MAX = 20; // 0 - без ограничений
	public const AVAILABILITY_CACHE_TTL = 300;

	// репозитории
	protected $shopGroupMoveRepository;
	protected $shopNetworkRepository;
	protected $shopStockRepository;
	protected $shopRepository;
	protected $orderTypeRepository;

	/**
	 * @var CacheInterface
	 */
	protected $cacheComponent;

	protected $localCache = [];

	public function __construct(ShopGroupMoveRepositoryInterface $shopGroupMoveRepository,
	                            ShopNetworkRepositoryInterface $shopNetworkRepository,
	                            ShopStockRepositoryInterface $shopStockRepository,
	                            ShopRepositoryInterface $shopRepository,
	                            OrderTypeRepositoryInterface $orderTypeRepository
	)
	{

		$this->shopGroupMoveRepository = $shopGroupMoveRepository;
		$this->shopNetworkRepository = $shopNetworkRepository;
		$this->shopStockRepository = $shopStockRepository;
		$this->shopRepository = $shopRepository;

		$this->orderTypeRepository = $orderTypeRepository;

		$this->cacheComponent = Yii::$app->cacheAvailability;
	}

	/**
	 * @param array $availability
	 * @return array
	 * @throws Exception
	 */
	protected function preparePickup($availability)
	{

		if (!isset($availability['availability'])) {
			return [];
		}

		$currentDate = new DateTime();
		$currentDate->setTime(0, 0, 0);

		$data = [];

		foreach ($availability['availability'] as $shopData) {

			$days = $shopData['days'] + (int)($availability['days'] ?? 0);
			$qty = $shopData['qty'];

			$dt = clone $currentDate;
			if ($days > 0) {
				$dt->modify("+{$days} day");
			}
			if (!isset($data[$shopData['shop_id']][(string)$days])) {
				$data[$shopData['shop_id']][(string)$days] = [
					'day' => $days,
					'quantity' => 0,
					'date' => $dt->format('r'),
				];
			}
			$data[$shopData['shop_id']][(string)$days]['quantity'] += $qty;
		}

		foreach (array_keys($data) as $shop_id) {

			ksort($data[$shop_id]);

			$prevIndex = null;
			foreach (array_keys($data[$shop_id]) as $nDay) {
				if (null !== $prevIndex) {
					$data[$shop_id][$nDay]['quantity'] += $data[$shop_id][$prevIndex]['quantity'];
				}
				$prevIndex = $nDay;
			}

			if ((int)static::VIEW_MAX > 0) {
				$data[$shop_id] = array_map(static function ($v) {
					$v['quantity'] = min($v['quantity'], static::VIEW_MAX);
					return $v;
				}, $data[$shop_id]);
			}
		}

		$shops = [];

		if ([] !== $data) {
			$shops = Shop::find()
				->published()
				->byId(array_keys($data))
				->indexBy('shop_id')
				->defaultOrder()
				->with(['network' => function (ShopNetworkQuery $q) {
					$q->defaultOrder();
				}])
				->all();
		}

		$data = array_intersect_key($data, $shops);

		$networks = [];
		foreach ($shops as $i => $shop) {
			if ($shop->network != null && !isset($networks[$shop->network->getId()])) {
				$networks[$shop->network->getId()] = $shop->network;
			}
			// Под вопросом расширенное поле 'groupBy'
			$shops[$i] = $shop->toArray([], ['groupBy']);
		}

		return [] === $data ? [] : [
			'shops' => array_values($shops),
			'networks' => array_values($networks),
			'data' => $data,
		];
	}

	/**
	 * @param array $availability
	 * @return array
	 * @throws Exception
	 */
	protected function prepareDelivery($availability)
	{
		if (!isset($availability['availability'])) {
			return [];
		}

		$currentDate = new DateTime();
		$currentDate->setTime(0, 0, 0);

		$data = [];

		foreach ($availability['availability'] as $shopData) {

			$days = $shopData['days'] + (int)($availability['days'] ?? 0);
			$qty = $shopData['qty'];

			$dt = clone $currentDate;
			if ($days > 0) {
				$dt->modify("+{$days} day");
			}

			if (!isset($data[(string)$days])) {
				$data[(string)$days] = [
					'day' => $days,
					'quantity' => 0,
					'date' => $dt->format('r'),
				];
			}

			$data[(string)$days]['quantity'] += $qty;
		}

		ksort($data);

		$prevIndex = null;
		foreach (array_keys($data) as $nDay) {
			if (null !== $prevIndex) {
				$data[$nDay]['quantity'] += $data[$prevIndex]['quantity'];
			}
			$prevIndex = $nDay;
		}
		return $data;
	}

	/**
	 * @param GoodEntityInterface $good
	 * @param RegionEntityInterface $region
	 * @return array
	 * @throws Exception
	 */
	public function getAvailability(GoodEntityInterface $good, RegionEntityInterface $region): array
	{
		return $this->getAvailabilityByGoodIdAndRegionZoneId($good->getId(), $region->getZoneId());
	}

	/**
	 * @param string $goodId
	 * @param int $regionZoneId
	 * @return array
	 * @throws Exception
	 */
	public function getAvailabilityByGoodIdAndRegionZoneId($goodId, $regionZoneId): array
	{
		$data = [];
		$available = $this->getRealAvailability($goodId, $regionZoneId);

		if ($available === []) {
			return $data;
		}

		// Получаем все доступные способы доставки
		$orderTypes = \common\models\OrderType::find()
			->byId(array_keys($available))
			->defaultOrder()
			->all();

		$profileKey = implode('_', [__CLASS__, __METHOD__]);
		foreach ($orderTypes as $orderType) {

			Yii::beginProfile("{$profileKey}_{$orderType->category}");

			switch ($orderType->category) {

				case \common\models\OrderType::CATEGORY_PICKUP :
					$result = $this->preparePickup($available[$orderType->getId()]);
					if ($result !== []) {
						$data[$orderType->id] = [
							'type' => $orderType->toArray(),
							'data' => $result,
						];
					}
					break;

				case \common\models\OrderType::CATEGORY_CITY:
				case \common\models\OrderType::CATEGORY_REGION:
				case \common\models\OrderType::CATEGORY_RUSSIA:
				case \common\models\OrderType::CATEGORY_RUSSIA_TC:
					$data[$orderType->id] = [
						'type' => $orderType->toArray(),
						'data' => $this->prepareDelivery($available[$orderType->getId()]),
					];
					break;
			}
			Yii::endProfile("{$profileKey}_{$orderType->category}");
		}
		return array_values(ArrayHelper::toArray($data));
	}

	public function getShopStockTotal($goodAvailability, int $zoneId): array
	{

		$orderTypes = $this->getOrderTypes();

		/**
		 * @var OrderType $orderType
		 */
		$av_real = [];
		foreach ($orderTypes as $orderType) {

			$from_shop_id = $orderType->getFromShopId();
			$av_ot = [];

			foreach ($goodAvailability as $av) {
				$shopId = $av['shop_id'];
				$shopGroup = $this->getShopGroupByShopId($shopId);
				if ($shopGroup['zone_id'] == $zoneId && ($from_shop_id == 0 || $from_shop_id == $shopId)) {
					// Если нет такого элемента в массиве, создаетм
					if (!isset($av_ot[$shopId])) {
						$av_ot[$shopId] = 0;
					}
					$av_ot[$shopId] += $av['amount'];
				}
			}
			if ([] !== $av_ot) {
				$av_real[$orderType->getId()] = $av_ot;
			}
		}

		return $av_real;
	}

	/**
	 * Return order type stock
	 * @param string $goodId
	 * @param int $zoneId
	 * @return array
	 * @throws Exception
	 */
	public function getOrderTypeStock($goodId, int $zoneId): array
	{

		$availability = $this->getAvailableByGoodId($goodId, false, true);

		/**
		 * @var OrderType $orderType
		 */
		$goodAvailability = $this->getExpandedAvailability($availability, $goodId, $zoneId);

		$shopStockTotal = $this->getShopStockTotal($goodAvailability, $zoneId);

		$shopStockTotal = array_map(static function ($v) {
			return max(array_values($v));
		}, $shopStockTotal);

		return $shopStockTotal;

	}

	/**
	 * @param $goodId
	 * @param int $zoneId
	 * @return array
	 * @throws Exception
	 */
	public function getRealAvailability($goodId, int $zoneId): array
	{

		$orderTypes = $this->getOrderTypes();

		$availability = $this->getAvailableByGoodId($goodId, false, true);
		$av_raw_expanded = $this->getExpandedAvailability($availability, $goodId, $zoneId);

		/**
		 * @var OrderType $orderType
		 */

		$av_real = [];
		foreach ($orderTypes as $orderType) {

			$from_shop_id = $orderType->getFromShopId();
			$av_ot = [];

			foreach ($av_raw_expanded as $k => $av) {

				$shopId = $av['shop_id'];
				$shopGroup = $this->getShopGroupByShopId($shopId);

				if ($shopGroup['zone_id'] == $zoneId && ($from_shop_id == 0 || $from_shop_id == $shopId)) {

					$key = $av['shop_id'] . '_' . $av['days'];

					// Если нет такого элемента в массиве, создаетм шаблон
					if (!isset($av_ot[$key])) {
						$av_ot[$key] = [
							'shop_id' => $av['shop_id'],
							'qty' => 0,
							'days' => 0,
							'from_shop_id' => [],
						];
					}

					$av_ot[$key]['qty'] += $av['amount'];

					// это для того что-бы переопределить 0, который ставиться по-умолчанию
					// теоретически можно сразу нормальное значение выставлять
					$av_ot[$key]['days'] = max($av_ot[$key]['days'], $av['days']);

					// Если в списке магазинов нет данного магазина, добавляем его
					if (!in_array($av['from_shop_id'], $av_ot[$key]['from_shop_id'])) {
						$av_ot[$key]['from_shop_id'][] = $av['from_shop_id'];
					}
				}
			}

			if ([] !== $av_ot) {
				$av_real[$orderType->getId()] = [
					'name' => $orderType->getTitle(),
					'availability' => $av_ot,
					'days' => $orderType->getDays(),
				];
			}
		}
		return $av_real;
	}

	/**
	 * @param GoodEntityInterface $good
	 * @param int $zoneId
	 * @return array
	 * @throws Exception
	 * @deprecated ???
	 */
	public function getRealAvailabilityOrder(GoodEntityInterface $good, int $zoneId): array
	{
		return $this->getRealAvailabilityOrderByGoodIdAndZoneId($good->getId(), $zoneId);
	}

	/**
	 * @param $goodId
	 * @param int $zoneId
	 * @return array
	 * @throws Exception
	 */
	public function getRealAvailabilityOrderByGoodIdAndZoneId($goodId, int $zoneId): array
	{

		$orderTypes = $this->getOrderTypes();

		$availability = $this->getAvailableByGoodId($goodId, false, true);
		$avRawExpanded = $this->getExpandedAvailability($availability, $goodId, $zoneId);

		/**
		 * @var OrderType $orderType
		 */

		$avReal = [];
		foreach ($orderTypes as $orderType) {

			$fromShopId = $orderType->getFromShopId();
			$avOt = [];

			foreach ($avRawExpanded as $k => $av) {

				$shopId = $av['shop_id'];
				$shopGroup = $this->getShopGroupByShopId($shopId);

				if ($shopGroup['zone_id'] == $zoneId && ($fromShopId == 0 || $fromShopId == $shopId)) {
					$key = implode('_', [$av['shop_id'], $av['days'], $av['from_shop_id']]);
					$avOt[$key] = $av;
				}
			}

			if ([] !== $avOt) {
				$avReal[$orderType->getId()] = [
					'name' => $orderType->getTitle(),
					'availability' => $avOt,
					'days' => $orderType->getDays(),
				];
			}
		}

		return $avReal;
	}

	/**
	 * @param array $availabilityData
	 * @return array
	 */
	protected function sortShopsAvailabilityLogic(array $availabilityData): array
	{

		$arrayOrderBy = static function () {

			$args = func_get_args();

			// Первым параметром идёт массив для сортировки, извлекаем его
			$data = array_shift($args);

			// преобразовываем название колонок в массив значений этих колонок
			foreach ($args as $n => $field) {

				if (!is_string($field)) {
					continue;
				}

				$tmp = [];
				foreach ($data as $key => $row) {
					$tmp[$key] = (int)($row[$field] ?? 0);
				}

				$args[$n] = $tmp;
				//$args[$n] = array_column($data, $field);
			}

			// для функции сортировки данные должны быть в конце
			$args[] = &$data;

			// сортируем массив
			array_multisort(...$args);

			// возвращаем отсортированные данные
			return array_pop($args);
		};

		return $arrayOrderBy(
			$availabilityData,
			'shop_id', SORT_ASC,
			'days', SORT_ASC,
			'priority', SORT_ASC,
			'amount', SORT_DESC
		);
	}

	/**
	 * @param array $available
	 * @param $goodId
	 * @param int $zoneId
	 * @param bool $useCache
	 * @return array
	 * @throws Exception
	 */
	public function getExpandedAvailability(array $available, $goodId, int $zoneId, $useCache = true): array
	{

		$av_withinzone = $this->expandAvailabilityWithZoneInsides($available, $zoneId);
		$av_crosszones_extra = $this->getCrossZonesMovementsExtraAv($available);

		return $this->sortShopsAvailabilityLogic(array_merge($av_withinzone, $av_crosszones_extra));
	}

	/**
	 * строим матрицу перемещений внутри группы
	 * @param array $inputAvailable
	 * @param null $onlyZoneId
	 * @return array|mixed
	 * @throws Exception
	 */
	protected function getExtraAvailability(array $inputAvailable, $onlyZoneId = null)
	{

		$extra = [];

		$shopsAndGroups = $this->_fetchShopAndGroups();

		/**
		 * @var ShopAndGroup $shop
		 */
		foreach ($shopsAndGroups['shops'] as $shopId => $shop) {

			// Если ограничение по зоне задано, считаем, только по заданной зоне
			if ($onlyZoneId !== null && $shop->zoneId != $onlyZoneId) {
				continue;
			}

			$qty = 0;

			$checkShopKey = $shopId . '_0';
			if (isset($inputAvailable[$checkShopKey])) {
				$qty = $inputAvailable[$checkShopKey]['amount'];
			}

			// Если кол-во < 20
			if ($qty < 20) {

				$x = 0;
				$shopGroup = $this->getShopGroupByShopId($shopId);
				foreach ($shopGroup['shops'] as $shopIdInGroup) {

					if ($shopId == $shopIdInGroup || !array_key_exists($shopIdInGroup . '_0', $inputAvailable)) {
						continue;
					}

					$v = $inputAvailable[$shopIdInGroup . '_0'];

					$shopKey = $shopId . '_1';
					if (!isset($extra[$shop->zoneId][$shopKey])) {
						$x = 0;
						$extra[$shop->zoneId][$shopKey] = [
							'shop_id' => $shopId,
							'amount' => $v['amount'],
							'days' => $this->addTimeCorrectionIfNotZero(1),
							'from_shop_id' => $shopIdInGroup,
						];
					} else {

						$x++;
						$shopKey .= '_' . $x;

						$extra[$shop->zoneId][$shopKey] = [
							'shop_id' => $shopId,
							'amount' => $v['amount'],
							'days' => $this->addTimeCorrectionIfNotZero(1),
							'from_shop_id' => $shopIdInGroup,
						];
					}
				}
			}
		}

		return $onlyZoneId !== null ? ArrayHelper::getValue($extra, $onlyZoneId, []) : $extra;
	}

	/**
	 * @param array $inputAvailable
	 * @param int $zoneId
	 * @return array
	 * @throws Exception
	 */
	protected function expandAvailabilityWithZoneInsides(array $inputAvailable, int $zoneId)
	{
		return array_merge($inputAvailable, $this->getExtraAvailability($inputAvailable, $zoneId));
	}

	/**
	 * @param array $av_arr
	 * @return array
	 * @throws Exception
	 */
	protected function getCrossZonesMovementsExtraAv(array $av_arr)
	{
		$extra = [];
		$shopGroupMoves = $this->_fetchShopGroupMoves();
		foreach ($av_arr as $av) {

			$shopId = $av['shop_id'];
			$qty = $av['amount'];

			$group_from = $this->getShopGroupByShopId($shopId);
			$groupId_from = $group_from['group_id'];

			if (!isset($shopGroupMoves[$groupId_from])) {
				continue;
			}
			foreach ($shopGroupMoves[$groupId_from] as $groupId_to => $shopGroupData) {
				$group_to = $this->getShopGroupById($groupId_to);
				if ($group_to !== null) {
					foreach ($group_to as $shopId_to) {
						$extra[$shopId_to . '_C_' . $shopId] = [
							'shop_id' => $shopId_to,
							'amount' => $qty,
							'days' => $this->addTimeCorrectionIfNotZero($shopGroupData['days']),
							'priority' => $shopGroupData['priority'],
							'from_shop_id' => $shopId,
						];
					}
				}
			}
		}
		return $extra;
	}

	/**
	 * @param int $shopId
	 * @return array|null
	 */
	public function getShopGroupByShopId(int $shopId)
	{
		$shopsAndGroups = $this->_fetchShopAndGroups();
		$shopGroup = null;
		if (isset($shopsAndGroups['shops'][$shopId])) {
			$groupId = $shopsAndGroups['shops'][$shopId]->groupId;
			if (isset($shopsAndGroups['groups'][$groupId])) {
				$shopGroup = [
					'group_id' => $groupId,
					'zone_id' => $shopsAndGroups['shops'][$shopId]->zoneId,
					'name' => $shopsAndGroups['shops'][$shopId]->title,
					'shops' => $shopsAndGroups['groups'][$groupId],
				];
			}
		}
		return $shopGroup;
	}

	/**
	 * @param int $groupId
	 * @return array|null
	 */
	public function getShopGroupById(int $groupId)
	{
		$shopsAndGroups = $this->_fetchShopAndGroups();
		return $shopsAndGroups['groups'][$groupId] ?? null;
	}

	/**
	 * @param $goodId
	 * @param bool $isPreorder
	 * @return string
	 */
	public static function getAvailableRawCacheKey($goodId, $isPreorder = false)
	{
		$keyPart = ['AV_RAW', $goodId];
		if ($isPreorder) {
			$keyPart[] = 'PRE';
		}
		return implode('_', $keyPart);
	}

	/**
	 * @param $goodId
	 * @param $regionZoneId
	 * @return string
	 * @deprecated
	 */
	public static function getAvailableExtendedCacheKey($goodId, $regionZoneId)
	{
		return implode('_', ['AVX', $regionZoneId, $goodId]);
	}

	/**
	 * @param $goodId
	 * @param $regionZoneId
	 * @return string
	 * @deprecated
	 */
	public static function getAvailableRealCacheKey($goodId, $regionZoneId)
	{
		return implode('_', ['real_av', $goodId, $regionZoneId]);
	}

	/**
	 * @param int $nDays
	 * @return int
	 * @throws Exception
	 */
	protected function addTimeCorrectionIfNotZero(int $nDays)
	{
		if ($nDays > 0) {
			$nDays += $this->getDaytimeCorrection();
		}
		return $nDays;
	}

	/**
	 * Если заказ пришел после XX дня, значит прибалвяем ещё день
	 * @return int
	 * @throws Exception
	 */
	protected function getDaytimeCorrection()
	{
		$dt = (new DateTime())->setTime(17, 0, 0);
		$nExtraDays = 0;
		if (time() >= $dt->getTimestamp()) {
			$nExtraDays = 1;
		}
		return $nExtraDays;
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function getAvailablePreorderFromCache($id)
	{
		if (false !== ($data = $this->cacheComponent->get(static::getAvailableRawCacheKey($id, true)))) {
			return $this->unserializeAvailability($data);
		}
		return [];
	}

	/**
	 * @param $id
	 * @param bool $refresh
	 * @param bool $without10kShops
	 * @return mixed
	 */
	public function getAvailableByGoodId($id, bool $refresh = false, $without10kShops = true)
	{

		$idKey = $id . '-' . (int)$without10kShops;
		if (!isset($this->localCache[__METHOD__][$idKey]) || $refresh === true) {

			$this->localCache[__METHOD__][$idKey] = [];

			$cacheKey = static::getAvailableRawCacheKey($id);
			if (false === ($data = $this->cacheComponent->get($cacheKey))) {

				$shopStocks = $this->shopStockRepository->findAllByGoodId($id);

				/** @var ShopStock $shop */
				foreach ($shopStocks as $shop) {
					$_shopId = $shop->getShopId();
					$this->localCache[__METHOD__][$idKey][$_shopId . '_0'] = [
						'shop_id' => $_shopId,
						'amount' => $shop->getAmount(),
						'days' => 0,
						'from_shop_id' => $_shopId,
					];
				}

				$this->cacheComponent->set($cacheKey, $this->serializeAvailability($this->localCache[__METHOD__][$idKey]), static::AVAILABILITY_CACHE_TTL);

				// preorder data сохраняю, чтобы добавлять эти данные
				// при обновлении наличия из 1с во время заказа
				$preorderData = array_filter($this->localCache[__METHOD__][$idKey], static function ($v) {
					return $v['shop_id'] >= 10000;
				});
				$this->cacheComponent->set(static::getAvailableRawCacheKey($id, true), $this->serializeAvailability($preorderData));

				Yii::info(['FROM DB' => ['good' => $id, 'stock' => $this->localCache[__METHOD__][$idKey]]], '_fetchAvailableByGoodId');
			} else {

				$this->localCache[__METHOD__][$idKey] = $this->unserializeAvailability($data);
				Yii::info(['FROM CACHE' => ['good' => $id, 'stock' => $this->localCache[__METHOD__][$idKey]]], '_fetchAvailableByGoodId');
			}

			if ($without10kShops === true) {
				$this->localCache[__METHOD__][$idKey] = array_filter($this->localCache[__METHOD__][$idKey], static function ($v) {
					return $v['shop_id'] < 10000;
				});
			}
		}

		return $this->localCache[__METHOD__][$idKey];
	}

	/**
	 * @return ShopAndGroup[]
	 */
	protected function _fetchShopAndGroups()
	{
		static $data;
		if (null === $data) {
			$data = $this->cacheComponent->getOrSet(implode('_', [__CLASS__, __METHOD__]), static function () {

				$data = [
					'groups' => [],
					'shops' => [],
				];

				$reader = Shop::find()->getShopAndGroup();
				foreach ($reader->each() as $row) {

					/**
					 * @var ShopAndGroup $entity
					 */
					$entity = new ShopAndGroup($row['shop_id'], $row['group_id'], $row['zone_id'], $row['title']);

					$groupId = $entity->groupId;
					$shopId = $entity->shopId;

					if (!isset($data['groups'][$groupId])) {
						$data['groups'][$groupId] = [];
					}

					$data['groups'][$groupId][] = $shopId;

					$data['shops'][$shopId] = $entity;
				}

				return $data;
			});
		}
		return $data;
	}

	/**
	 * @return mixed
	 */
	protected function _fetchShopGroupMoves()
	{
		static $data;
		if (null === $data) {
			$data = $this->cacheComponent->getOrSet(implode('_', [__CLASS__, __METHOD__]), function () {

				$data = [];
				$groupMovies = $this->shopGroupMoveRepository->findAll();

				/** @var ShopGroupMove $row */
				foreach ($groupMovies as $row) {

					$shop_group_from = $row->getGroupIdFrom();
					$shop_group_to = $row->getGroupIdTo();

					if (!isset($data[$shop_group_from])) {
						$data[$shop_group_from] = [];
					}

					$data[$shop_group_from][$shop_group_to] = [
						'move_id' => $row->getId(),
						'days' => $row->getDays(),
						'priority' => $row->getPriority(),
					];
				}
				return $data;
			});
		}
		return $data;
	}

	/**
	 * @return array
	 */
	public function getOrderTypes(): array
	{
		static $data;
		if (null === $data) {
			$data = $this->cacheComponent->getOrSet(implode('_', [__CLASS__, __METHOD__]), static function () {
				return \common\models\OrderType::find()->defaultOrder()->all();
			});
		}
		return $data;
	}

	/**
	 * @param $av
	 * @return string
	 */
	protected function serializeAvailability($av)
	{
		// сортируем по магазину
		usort($av, static function ($a, $b) {
			return $a['shop_id'] <=> $b['shop_id'];
		});
		$avData = [];
		foreach ($av as $a) {
			$avData[] = $a['shop_id'] . ':' . $a['amount'];
		}
		return implode("\t", $avData);
	}

	/**
	 * @param $avData
	 * @return array
	 */
	protected function unserializeAvailability($avData)
	{
		$av = [];
		if (empty($avData)) {
			return $av;
		}
		$avTmp = explode("\t", $avData);
		foreach ($avTmp as $a) {
			$v = explode(':', $a);
			$av[$v[0] . '_0'] = [
				'shop_id' => (int)$v[0],
				'amount' => $v[1],
				'days' => 0,
				'from_shop_id' => (int)$v[0],
			];
		}
		return $av;
	}

	/**
	 * @param $goodId
	 * @param $regionZoneId
	 * @param array $data
	 */
	public function updateCache($goodId, $regionZoneId, array $data)
	{
		$serializedData = $this->serializeAvailability($data);
		// добавляем к новым данным, данные о предзаказе из кеша
		/*		$_c = $this->cacheComponent->get(static::getAvailableRawCacheKey($goodId, true));
				if (false !== $_c && !empty($_c)) {
					$serializedData .= (!empty($serializedData) ? "\t" : '') . $_c;
				}*/
		$this->cacheComponent->set(static::getAvailableRawCacheKey($goodId), $serializedData, static::AVAILABILITY_CACHE_TTL);
		$this->flushLocalCache();
	}

	/**
	 * Flush availability raw data cache
	 */
	protected function flushLocalCache(): void
	{
		$this->localCache = [];
	}

}
