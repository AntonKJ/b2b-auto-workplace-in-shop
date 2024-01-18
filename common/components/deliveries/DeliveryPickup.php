<?php

namespace common\components\deliveries;

use common\components\deliveries\forms\DeliveryPickupForm;
use common\components\deliverySchedule\DeliveryScheduleInterface;
use common\components\payments\PaymentCash;
use common\components\payments\PaymentInvoice;
use common\components\QuarantineProvider;
use common\models\OrderType;
use common\models\OrderTypeGroup;
use common\models\Shop;
use DateInterval;
use DateTime;
use Exception;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;
use function in_array;
use function is_array;

class DeliveryPickup extends DeliveryAbstract
{

	// Колво дней на резерв
	public const RESERV_DAYS = 2;

	// Отображение периода на календаре +дней
	public const CALENDAR_RANGE_DAYS = 5;

	/**
	 * @var integer
	 */
	public $deliveryMinimumDays;

	/**
	 * @var DateTime
	 */
	public $dateFrom;

	/**
	 * @var OrderType
	 */
	private $_currentOrderType;

	public function init()
	{

		$this->deliveryMinimumDays = 0;
		$this->dateFrom = new DateTime();

		parent::init();
	}

	public function getShops($refresh = false)
	{

		static $shopsIds;

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');
		if (!is_array($shopsIds) || $refresh) {

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
				->orderByPriority()// сортируем по приоритету
				->byOrderTypeGroup($region)
				->byId($otIds);

			$orderTypes = [];
			foreach ($query->each() as $ot)
				$orderTypes[(int)$ot->getId()] = $ot;

			// Региональные магазины
			$regionShopIds = false;

			// Подготавливаем фильтр по региональным магазинам, кроме B2B и CC
			if (!($region->isRegionZoneTypeB2B || $region->isRegionZoneTypeCC)) {

				// Берем ID активных магазинов для региона
				$regionShopIds = Shop::find()
					->select(['shop_id'])
					->byRegion($region)
					->active()
					->column();

				$regionShopIds = array_fill_keys($regionShopIds, null);
			}

			$shopsIds = [];
			foreach ($orderTypes as $orderTypeId => $currentOrderType) {

				// Получаем список магазинов с наличием и днями когда можно забрать товар
				$shopsIds = $this->getDeliveryComponent()
					->getPickupStoresForGoods($this->goods->getData(), $region->getZoneId(), $orderTypeId);

				// Если фильтр по региональным магазинам активен, фильтруем
				if ($regionShopIds !== false)
					$shopsIds = array_intersect_key($shopsIds, $regionShopIds);

				if (QuarantineProvider::isQuarantineModeActive() &&
					QuarantineProvider::quarantineSchema() === QuarantineProvider::SCHEMA_NO_PICKUPS) {
					$regionId = $region->getId();
					$shopsIds = array_filter($shopsIds, static function ($shop) use ($regionId) {
						return QuarantineProvider::isShopActive($regionId, $shop['shop_id']);
					});
				}

				if ($shopsIds !== []) {

					$this->_currentOrderType = $currentOrderType;
					break;
				}
			}

		}
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');

		return $shopsIds;
	}

	/**
	 * @param DateTime $fromDate
	 * @param $shopData
	 * @throws Exception
	 */
	public static function prepareShopDeliveryData(DateTime $fromDate, $shopData)
	{

		$minDays = $shopData['days'];
		$minDaysText = null;

		switch (true) {

			case $minDays <= 2:

				$minDaysText = ['сегодня', 'завтра', 'послезавтра'][$minDays];
				break;

			default:

				$weekDay = $fromDate->format('w');
				switch (true) {

					case in_array($weekDay, [4, 5]):

						$minDays += 2;
						break;

					case $weekDay == 6:

						$minDays += 1;
						break;
				}

				$minDaysText = (clone $fromDate)
					->add(new DateInterval("P{$minDays}D"))
					->format('d.m.Y');

				break;

		}

		$minDt = (clone $fromDate)->setTime(0, 0);

		if ($minDays > 0)
			$minDt
				->add(new DateInterval("P{$minDays}D"));

		$maxDays = $minDays + static::CALENDAR_RANGE_DAYS;

		$maxDt = (clone $fromDate)->setTime(23, 59, 59);

		if ($maxDays > 0)
			$maxDt->add(new DateInterval("P{$maxDays}D"));

		return [
			'amount' => $shopData['amount'],
			'deliveryDate' => [
				'min' => [
					'day' => $minDays,
					'dayDatetime' => $minDt->format('r'),
					'dayText' => $minDaysText,
				],
				'max' => [
					'day' => $maxDays,
					'dayDatetime' => $maxDt->format('r'),
					'dayText' => $maxDt->format('d.m.Y'),
				],
			],

		];

	}

	/**
	 * @return array|null
	 * @throws Exception
	 * @throws Throwable
	 */
	public function getData()
	{

		static $data;

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');
		if ($data === null) {

			$data = [
				'items' => [],
			];

			$today = new DateTime();

			$shopsIds = $this->getShops();

			if ([] !== $shopsIds) {

				$query = Shop::find()
					->byId(array_keys($shopsIds))
					->defaultOrder()
					->published();

				foreach ($query->each() as $shop) {

					if (!isset($shopsIds[$shop->getId()])) {
						continue;
					}

					$info = static::prepareShopDeliveryData($today, $shopsIds[$shop->getId()]);
					$info['shop'] = $shop->toArray();

					$data['items'][] = $info;
				}

			}

			$data['active'] = isset($data['items']) && $data['items'] !== [];
			if ($data['active'] === false) {
				Yii::info("{$this->getTitle()} недоступен, т.к. нет доступных магазинов.");
			}

			$payments = $this->getPayments();
			if ($payments === []) {

				Yii::info("{$this->getTitle()} недоступен, т.к. нет доступных способов оплаты для пользователя.");
				$data['active'] = false;
			}

			if (QuarantineProvider::isQuarantineModeActive() &&
				QuarantineProvider::quarantineSchema() === QuarantineProvider::SCHEMA_NO_CASH_PAYMENT) {
				$noCash = array_values(array_filter($payments, static function ($p) {
					return !($p instanceof PaymentCash);
				}));
				$regionId = $this->getRegion()->getId();
				foreach (array_keys($data['items']) as $shopIdx) {
					if (!QuarantineProvider::isShopActive($regionId, $data['items'][$shopIdx]['shop']['id'])) {
						$data['items'][$shopIdx]['shop']['payments'] = $noCash;
					}
				}
			}

			$data['payments'] = $payments;

			$data['schedules'] = $this->getDeliverySchedule();
		}
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');

		return $data;
	}

	/**
	 * @return array|mixed|null
	 * @throws Throwable
	 */
	public function getDataForClient()
	{

		$data = $this->getData();

		$data['schedules'] = array_values($data['schedules']);

		return $data;
	}

	/**
	 * @return bool
	 * @throws Throwable
	 */
	public function isActive(): bool
	{

		$active = true;

		$active = $active && $this->getData()['active'];

		return $active;
	}

	public function getTitle(): string
	{
		return 'Самовывоз';
	}

	static public function getCategory(): string
	{
		return OrderType::CATEGORY_PICKUP;
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
	 * @return array|DeliveryScheduleInterface[]
	 */
	protected function getDeliverySchedule()
	{

		$schedule = [];

		// Чтобы  определился _currentOrderType
		$this->getShops();

		if (null !== $this->_currentOrderType)
			$schedule = ArrayHelper::index($this->_currentOrderType->getDeliverySchedule(), 'id');

		return $schedule;
	}

	/**
	 * @return forms\DeliveryFormInterface|DeliveryPickupForm
	 * @throws Exception
	 * @throws Throwable
	 */
	public function getFormModel()
	{
		return new DeliveryPickupForm($this);
	}


}
