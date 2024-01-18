<?php

namespace common\components\deliveries;

use common\components\deliveries\forms\DeliveryRussiaForm;
use common\components\payments\PaymentCash;
use common\components\payments\PaymentInvoice;
use common\models\DeliveryCitySst;
use common\models\OrderType;
use common\models\OrderTypeGroup;
use common\models\Shop;
use DateTime;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

class DeliveryRussia extends DeliveryAbstract
{

	public const MAX_RESERVE_DAYS = 9;

	// Отображение периода на календаре +дней
	public const CALENDAR_RANGE_DAYS = 5;

	protected $_activeOrderTypes;

	/**
	 * @return array
	 * @throws Throwable
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 */
	public function getActiveOrderTypes(): array
	{
		if (!is_array($this->_activeOrderTypes))
			$this->getData();

		return $this->_activeOrderTypes;
	}

	/**
	 * @return array
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 */
	public function getData()
	{

		static $data;

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');
		if (null === $data) {

			$user = $this->getUser();
			$region = $this->getRegion();

			/**
			 * @var int[] $orderTypeGroup
			 */
			$orderTypeGroup = [$region->getOrderTypeGroupId()];

			if ($user !== null && $user->category !== null)
				$orderTypeGroup[] = $user->getOrderTypeGroupId();

			$otIds = OrderTypeGroup::calculateOrderTypeGroupIntersect($orderTypeGroup);

			$query = OrderType::find()
				->byCategory(static::getCategory())
				->defaultOrder()
				->byOrderTypeGroup($region)
				->byId($otIds);

			$orderTypeByShops = [];
			foreach ($query->each() as $ot)
				$orderTypeByShops[(int)$ot->from_shop_id][(int)$ot->getId()] = $ot;

			// Берем ID активных магазинов для региона
			$regionShopIds = Shop::find()
				->select(['shop_id'])
				->active()
				->column();

			$regionShopIds = array_fill_keys($regionShopIds, null);

			$orderTypeByShops = array_intersect_key($orderTypeByShops, $regionShopIds);

			$orderTypesCollection = [];
			$shopsByOrderType = [];

			foreach ($orderTypeByShops as $shopId => $ots)
				foreach ($ots as $otId => $ot) {

					$shopsByOrderType[$otId][] = $shopId;

					if (!isset($orderTypesCollection[$otId]))
						$orderTypesCollection[$otId] = $ot;
				}

			// Получаем доступные типы доставок и кол-во дней
			$activeOrderTypes = $this->getDeliveryComponent()
				->getOrderTypesForGoods($this->goods->getData(), $region->getZoneId());

			// Фильтруем по доступным типам доставок
			$shopsByOrderType = array_intersect_key($shopsByOrderType, $activeOrderTypes);

			$active = false;

			$today = new DateTime();

			$this->_activeOrderTypes = [];

			$cities = [];
			foreach ($shopsByOrderType as $otId => $shopsIds) {

				// Получаем список магазинов с наличием и днями когда можно забрать товар
				$shopsWithGoods = $this->getDeliveryComponent()
					->getPickupStoresForGoods($this->goods->getData(), $region->getZoneId(), $otId);

				// Фильтруем магазины, где есть товар
				$shopsWithGoods = array_intersect_key($shopsWithGoods, array_fill_keys($shopsIds, null));

				// Если нет магазинов с товаром, переходим дальше
				if ([] === $shopsWithGoods)
					continue;

				$active = true;

				$regionsCityQuery = DeliveryCitySst::find()
					->byIsActive()
					->byZoneId($region->getZoneId())
					->defaultOrder();

				$regionsCity = $regionsCityQuery->all();

				foreach (array_keys($regionsCity) as $rKey) {

					// Ближайший день доставки
					$minDays = $regionsCity[$rKey]->getClosestDeliveryDay($activeOrderTypes[$otId]);
					$maxDays = $minDays + static::CALENDAR_RANGE_DAYS;

					$minDt = (clone $today)->setTime(0, 0);
					if ($minDays > 0)
						$minDt->modify("+{$minDays} days");

					$maxDt = (clone $today)->setTime(23, 59, 59);
					if ($maxDays > 0)
						$maxDt->modify("+{$maxDays} days");


					$regionsCity[$rKey] = $regionsCity[$rKey]->toArray();
					$regionsCity[$rKey]['orderTypeId'] = $otId;

					$regionsCity[$rKey]['deliveryDate'] = [
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

				$cities = array_merge($cities, $regionsCity);

				$this->_activeOrderTypes[] = $orderTypesCollection[$otId];

				//todo: подозреваю, что не нужно проходить по циклу до конца, можно остановиться
				//todo: на первом успешном типе заказа
				//todo: но это не точно
				//break;
			}

			$payments = $this->getPayments();
			if ($payments === []) {

				Yii::info("{$this->getTitle()} недоступна, т.к. нет доступных способов оплаты для пользователя.");
				$active = false;
			}

			$schedules = [];
			/** @var OrderType $ot */
			foreach ($this->getActiveOrderTypes() as $ot)
				$schedules[$ot->getId()] = $ot->getDeliverySchedule();

			$data = [
				'active' => $active,
				'cities' => $cities,
				'payments' => $payments,
				'schedules' => $schedules,
			];
		}
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');

		return $data;
	}

	public function getDataForClient()
	{

		$data = parent::getDataForClient();
		$data['schedules'] = (object)$data['schedules'];

		return $data;
	}

	/**
	 * @return bool
	 * @throws Throwable
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 */
	public function isActive(): bool
	{
		$data = $this->getData();
		return isset($data['active']) && $data['active'];
	}

	public function getTitle(): string
	{
		return 'Доставка в регионы';
	}

	static public function getCategory(): string
	{
		return OrderType::CATEGORY_RUSSIA;
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

			if ($this->getUser() !== null && $this->getUser()->category !== null) {

				$allowedTypes = $this->getUser()->getPaymentTypes();
				$allowedTypes = array_flip($allowedTypes);

				$types = array_intersect_key($types, $allowedTypes);
				$types = array_values($types);
			}
		}

		return $types;
	}

	/**
	 * @return DeliveryRussiaForm
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 */
	public function getFormModel()
	{
		$data = $this->isActive() ? $this->getData() : [];
		return new DeliveryRussiaForm($data['cities'] ?? [], $data['payments'] ?? [], $this->getActiveOrderTypes(), $data['schedules'] ?? []);
	}

}
