<?php

namespace common\components\deliveries\forms;

use common\components\deliveries\DeliveryInterface;
use common\components\ExpressDelivery;
use common\interfaces\PoiInterface;
use common\models\DeliveryCity;
use common\models\DeliveryZone;
use common\models\MetroStation;
use common\models\OptUserAddress;
use common\models\OrderType;
use common\models\query\OrderTypeQuery;
use DateTime;
use domain\entities\GeoPosition;
use Exception;
use myexample\ecommerce\deliverySchedule\DeliveryScheduleInterface;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use function count;
use function in_array;
use function is_array;

/* todo Требуется рефакторин после привязки городов к зонам,
 * т.е. теперь можно найти ближайшую POI в активных типах доставки и через него вычислить актуальный order_type
 */

class DeliveryCityRegionForm extends Model implements DeliveryFormInterface
{

	public const EXPRESS_DELIVERY_DATE_FORMAT = 'Y-m-d\TH:i:s.u\Z';

	use PaymentFormTrait;
	use DeliveryFormTrait;

	public $autoAddress;

	public $city;
	public $street;
	public $house;

	public $coords;

	public $date;
	public $payment;

	public $comment;
	public $schedule;

	public $expressDelivery;

	public $mapNotActive;

	protected $_zones;
	protected $_payments;

	protected $_delivery;

	public function __construct(DeliveryInterface $delivery, array $config = [])
	{

		$this->_delivery = $delivery;

		$data = $this->_delivery->getData();

		$this->_zones = $data['zones'] ?? [];
		$this->_payments = ArrayHelper::index($data['payments'] ?? [], 'id');

		parent::__construct($config);
	}

	public function attributeLabels()
	{
		return [

			'city' => 'Город',
			'street' => 'Улица',
			'house' => 'Номер дома',

			'coords' => 'Координаты точки доставки',
			'date' => 'Дата доставки',
			'payment' => 'Вариант оплаты',
			'comment' => 'Комментарий к заказу',
			'schedule' => 'Время доставки',

			'mapNotActive' => 'Карты не активны',

			'expressDelivery' => 'Экспресс доставка',

		];
	}

	public function getAddress()
	{
		return trim(implode(', ', [$this->city, $this->street, $this->house]));
	}

	/**
	 * @return array
	 * @deprecated use $this->_zones instead
	 */
	public function getZonesIdsOptions()
	{
		return $this->_zones;
	}

	/**
	 * @return bool
	 */
	protected function deliveryHasAreas()
	{
		static $result = null;
		if (null === $result) {
			$result = false;
			foreach ($this->_zones as $zone) {
				if (isset($zone['deliveryZones']) && is_array($zone['deliveryZones']) && [] !== $zone['deliveryZones']) {
					foreach ($zone['deliveryZones'] as $dZone) {
						if (isset($dZone['geometry']) && !empty($dZone['geometry'])) {
							$result = true;
							break 2;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @return array
	 */
	public function rules()
	{

		$expressDeliveryWhenFunc = static function (self $model) {
			return (bool)$model->expressDelivery === false;
		};

		return [

			[['city', 'street', 'house', 'comment', 'date'], 'trim'],

			[['mapNotActive'], 'boolean'],
			[['mapNotActive'], 'default', 'value' => false],

			[['comment'], 'string', 'max' => 1000],

			[['autoAddress'], 'filter', 'filter' => static function ($v) {
				if (is_array($v) && isset($v['addressText'])) {
					$v = trim((string)$v['addressText']);
					if (!empty($v)) {
						return $v;
					}
				}
				return null;
			}],
			[['autoAddress'], 'string', 'max' => 1000],

			[['city'], 'required', 'message' => 'Укажите город'],
			[['city'], 'string', 'max' => 128],

			[['street'], 'required', 'message' => 'Укажите улицу'],
			[['street'], 'string', 'max' => 128],

			[['house'], 'required', 'message' => 'Укажите номер дома'],
			[['house'], 'string', 'max' => 16],

			[['coords'], 'required', 'message' => 'Установите точку доставки на карте.', 'when' => static function ($model) {
				return $model->mapNotActive == false;
			}],

			[['coords'], 'required', 'message' => 'Укажите ориентир для доставки (город или ст. метро).', 'when' => static function ($model) {
				return (bool)$model->mapNotActive === true;
			}],

			[['coords'], 'filter', 'filter' => static function ($v) {
				if (is_array($v) && isset($v['lat'], $v['lng'])) {
					$v = [$v['lat'], $v['lng']];
				}
				return array_values($v);
			}],
			[['coords'], 'validateCoords'],
			[['coords'], 'validateDeliveryZone'],

			[['expressDelivery'], 'filter', 'filter' => static function ($value) {
				return (bool)$value;
			}, 'skipOnEmpty' => false],

			[['expressDelivery'], 'validateExpressDelivery', 'when' => static function (self $model) {
				return (bool)$model->expressDelivery === true;
			}],

			[['date'], 'required', 'message' => 'Укажите дату доставки', 'when' => $expressDeliveryWhenFunc],
			[['date'], 'datetime', 'format' => static::JS_DATE_FORMAT, 'when' => $expressDeliveryWhenFunc],
			[['date'], 'validateDeliveryDate', 'when' => $expressDeliveryWhenFunc],

			[['schedule'], 'validateDeliverySchedule', 'when' => $expressDeliveryWhenFunc],

			[['payment'], 'required', 'message' => 'Выберите способ оплаты'],
			[['payment'], 'in', 'range' => array_keys($this->_payments)],

		];
	}

	public function afterValidate()
	{
		parent::afterValidate();
		// Применяем параметры экспресс доставки, если нет ошибок
		if (true === $this->expressDelivery && false === $this->hasErrors()) {
			$this->applyExpressDeliveryParams();
		}
	}

	public function validateCoords($attribute, $params, $validator)
	{
		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}
		$value = $this->{$attribute};
		if ($params['skipOnEmpty'] && empty($value)) {
			return;
		}
		if (!is_array($value) || count($value) !== 2) {
			$this->addError($attribute, 'Некорректные координаты точки доставки.');
		}
	}

	public function validateDeliveryZone($attribute, $params, $validator)
	{
		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}
		$orderType = $this->getOrderType();
		if ($orderType === null) {
			$this->addError($attribute, 'Доставка в данной точке недоступна.');
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws Exception
	 */
	public function validateDeliverySchedule($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}

		$value = $this->{$attribute};
		if (empty($value)) {
			return;
		}

		$scheduleOptions = [];

		/** @var OrderType $orderType */
		$orderType = $this->getOrderType();
		if ($orderType !== null) {

			$scheduleOptions = ArrayHelper::getColumn($orderType->getDeliverySchedule(), 'id', false);

			$currentDate = $this->getDateAsDateTime()->format('r');

			$scheduleDays = ExpressDelivery::resolveDaysSchedules(
				$this->_delivery->getRegion(),
				$orderType->getCategory(),
				$currentDate,
				$scheduleOptions
			);

			if ($scheduleDays !== null) {
				$scheduleOptions = array_values($scheduleDays[$currentDate]);
			}
		}

		if (!in_array($value, $scheduleOptions)) {
			$this->addError($attribute, "Неверное значение `{$this->getAttributeLabel($attribute)}`.");
		}
	}

	/**
	 * @return float
	 */
	public function getLatitude()
	{
		return (float)$this->coords[0];
	}

	/**
	 * @return float
	 */
	public function getLongitude()
	{
		return (float)$this->coords[1];
	}

	/**
	 * @return GeoPosition
	 */
	public function getGeoPosition()
	{

		static $position = false;

		if (false === $position)
			$position = new GeoPosition($this->getLatitude(), $this->getLongitude());

		return $position;
	}

	protected function getOrderTypeIds()
	{

		static $orderTypeIds = false;

		if (false === $orderTypeIds) {

			$position = $this->getGeoPosition();

			$zoneIds = array_keys($this->_zones);

			// Получаем все типы доставок в которых есть зоны с текущей точкой
			$orderTypeIds = DeliveryZone::find()
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
				->byGeoPosition($position)
				->groupBy('order_type_id')
				->andHaving('COUNT(dcr.delivery_city_id) > 0 OR category = :categoryCity', [':categoryCity' => OrderType::CATEGORY_CITY])
				->column();
		}

		return $orderTypeIds;
	}

	public function getClosestPoi()
	{

		static $closestPoi = false;

		if (false === $closestPoi) {

			$position = $this->getGeoPosition();

			// Получаем все типы доставок в которых есть зоны с текущей точкой
			$orderTypeIds = $this->getOrderTypeIds();

			$pointNotInZones = $orderTypeIds === [];

			// Если нет зон с текущей точкой, ставим все доступные зоны для текущей доставки
			if ($pointNotInZones)
				$orderTypeIds = array_keys($this->_zones);

			$closestPoiQuery = DeliveryCity::find()
				->byOrderTypeId($orderTypeIds)
				->orderByClosest($position)
				->limit(1);

			if ($pointNotInZones)
				$closestPoiQuery->byDistanceEqLess($position, DeliveryCity::AREA_RADIUS);

			$closestPoi = $closestPoiQuery->one();

			$closestMetroQuery = MetroStation::find()
				->byOrderTypeId($orderTypeIds)
				->orderByClosest($position)
				->limit(1);

			if ($pointNotInZones)
				$closestMetroQuery->byDistanceEqLess($position, MetroStation::AREA_RADIUS);

			$closestMetro = $closestMetroQuery->one();

			if ($closestPoi === null || ($closestMetro !== null && $closestPoi->getDistance() > $closestMetro->getDistance()))
				$closestPoi = $closestMetro;
		}

		return $closestPoi;
	}

	public function getOrderType()
	{

		static $orderType = false;

		if (false === $orderType) {

			$orderType = null;
			$poi = $this->getClosestPoi();

			if ($poi instanceof PoiInterface) {

				$orderTypeIds = $this->getOrderTypeIds();

				$pointNotInZones = $orderTypeIds === [];

				// Если нет зон с текущей точкой, ставим все доступные зоны для текущей доставки
				if ($pointNotInZones) {
					$orderTypeIds = array_keys($this->_zones);
				}


				// quick fix
				if ($poi instanceof MetroStation) {
					$orderTypeIdsPoi = $poi->getOrderTypeRel()
						->select(['order_type_id'])
						->andWhere(['order_type_id' => $orderTypeIds])
						->column();

					$orderType = OrderType::find()
						->byId($orderTypeIdsPoi)
						->orderByPriority()
						->limit(1)
						->one();
				} else {
					$orderType = $poi->getOrderTypeQuery()
						->byId($orderTypeIds)
						->orderByPriority()
						->limit(1)
						->one();
				}
			}
		}

		return $orderType;
	}

	public function getShopId()
	{
		return $this->getOrderType()->from_shop_id;
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws Exception
	 */
	public function validateExpressDelivery($attribute, $params, $validator)
	{

		if ($this->hasErrors()) {
			return;
		}

		$ot = $this->getOrderType();
		$poi = $this->getClosestPoi();

		$deliveryRangeData = $this->_zones[$ot->getId()]['deliveryDate'];
		if (isset($this->_delivery->getData()['poi'][$poi->getPoiType()][$poi->id]['fromZones'][$ot->getId()])) {
			$deliveryRangeData = $this->_delivery->getData()['poi'][$poi->getPoiType()][$poi->id]['fromZones'][$ot->getId()];
		}

		$expressDeliveryActive = ExpressDelivery::hasExpressDelivery(
			$this->_delivery->getRegion(),
			$ot->getCategory(),
			$deliveryRangeData['min']['dayDatetime']
		);

		if ($expressDeliveryActive === false) {
			$this->addError($attribute, 'Экспресс доставка не доступна!');
		}
	}

	protected function applyExpressDeliveryParams(): void
	{
		$this->date = (new DateTime())->format(static::EXPRESS_DELIVERY_DATE_FORMAT);

		/** @var OrderType $orderType */
		$orderType = $this->getOrderType();

		$scheduleOptions = ArrayHelper::index($orderType->getDeliverySchedule(), 'id');
		if ([] !== $scheduleOptions) {
			/** @var DeliveryScheduleInterface $lastScheduleElement */
			$lastScheduleElement = end($scheduleOptions);
			$this->schedule = $lastScheduleElement->getId();
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws Exception
	 */
	public function validateDeliveryDate($attribute, $params, $validator)
	{

		if ($this->hasErrors()) {
			return;
		}

		$ot = $this->getOrderType();
		$poi = $this->getClosestPoi();

		$deliveryRangeData = $this->_zones[$ot->getId()]['deliveryDate'];
		if (isset($this->_delivery->getData()['poi'][$poi->getPoiType()][$poi->id]['fromZones'][$ot->getId()]))
			$deliveryRangeData = $this->_delivery->getData()['poi'][$poi->getPoiType()][$poi->id]['fromZones'][$ot->getId()];

		$minDt = new DateTime($deliveryRangeData['min']['dayDatetime']);
		$maxDt = new DateTime($deliveryRangeData['max']['dayDatetime']);

		$dt = $this->getDateAsDateTime();

		if (!($dt->getTimestamp() >= $minDt->getTimestamp() && $maxDt->getTimestamp() >= $dt->getTimestamp()))
			$this->addError($attribute, "Выберите правильную дату доставки между {$minDt->format('d.m.Y')} и {$maxDt->format('d.m.Y')}, Вы выбрали {$dt->format('d.m.Y')}");

		$dayOfTheWeek = (int)$dt->format('N');

		if (!isset($poi->deliveryDays[$dayOfTheWeek])) {

			$days = [
				'понедельник',
				'вторник',
				'среду',
				'четверг',
				'пятницу',
				'субботу',
				'воскресение',
			];

			$errorMessage = ['Доставка осуществляется, только в определённые дни:'];
			$errorMessage[] = mb_strtolower(implode(', ', array_values($poi->deliveryDays))) . '.';
			$errorMessage[] = 'Вы указали ' . $days[$dayOfTheWeek - 1];

			$this->addError($attribute, implode(' ', $errorMessage));
		}
	}

	public function isAllowedAddressStore(): bool
	{
		return true;
	}

	public function loadAddressAttributes(OptUserAddress $addressModel): void
	{
		$addressModel->setAttributes([
			'type' => $this->_delivery::getCategory(),
			'address' => [
				'autoAddress' => $this->autoAddress,
				'fullAddress' => $this->getAddress(),
				'city' => $this->city,
				'street' => $this->street,
				'house' => $this->house,
				'coords' => $this->getGeoPosition()->toArray(),
			],
		], false);
	}

	public function getScheduleModel()
	{
		/** @var OrderType $orderType */
		$orderType = $this->getOrderType();
		if ($orderType === null) {
			return null;
		}

		$scheduleOptions = ArrayHelper::index($orderType->getDeliverySchedule(), 'id');
		return ArrayHelper::getValue($scheduleOptions, $this->schedule, null);
	}
}
