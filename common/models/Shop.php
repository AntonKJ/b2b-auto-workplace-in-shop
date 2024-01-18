<?php

namespace common\models;

use common\components\ecommerce\models\Shop as ShopEntity;
use common\interfaces\RegionEntityInterface;
use common\models\query\ShopQuery;
use domain\entities\GeoPosition;
use main\models\ShopsModel;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use function count;

/**
 * This is the model class for table "{{%shops}}".
 *
 * @property integer $shop_id
 * @property integer $region_id
 * @property integer $network_id
 * @property string $short_name
 * @property string $long_name
 * @property string $url
 * @property string $address
 * @property string $service_desc
 * @property string $working_hours
 * @property integer $credit_cards
 * @property string $map_coords
 * @property integer $num_lines
 * @property integer $owned_by
 * @property string $metro_color
 * @property string $location
 * @property string $address_brief
 * @property integer $zone_id
 * @property integer $not_show
 * @property integer $mounting_discount_4
 * @property integer $mounting_discount_8
 * @property string $message
 * @property integer $storing_discount_4
 * @property integer $storing_discount_8
 * @property string $phone
 * @property integer $shop_line_time_id_from
 * @property integer $shop_line_time_id_to
 * @property integer $shop_line_time_we_id_from
 * @property integer $shop_line_time_we_id_to
 * @property string $admin_email
 * @property integer $images_version
 * @property string $message2
 * @property string $action_desc
 * @property integer $shopgroup_id
 * @property integer $is_active
 * @property integer $is_new
 * @property integer $ord_num
 *
 * @property RegionEntityInterface $region
 *
 * @property bool isInMoscowPlaced текущий магазин находится в Москве?
 * @property string $service_name [varchar(63)]
 */
class Shop extends ActiveRecord
{

	public const IS_ACTIVE = 1;

	public const NOT_SHOW = 1;

	public const HAS_CARD_PAYMENT = 1;

	/**
	 * @var ShopEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): ShopEntity
	{

		if ($this->_ecommerceEntity === null) {
			$this->_ecommerceEntity = new ShopEntity(
				$this->getId(),
				(int)$this->getGroupId(),
				(int)$this->zone_id,
				(string)$this->getTitle(),
				(int)$this->not_show === static::NOT_SHOW,
				$this->getId() >= 10000
			);
		}
		return $this->_ecommerceEntity;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shops}}';
	}

	/**
	 * @inheritdoc
	 * @return ShopQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new ShopQuery(static::class);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getServicesRel()
	{
		return $this->hasMany(ShopService::class, ['shop_id' => 'shop_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getServices()
	{
		return $this->hasMany(Service::class, ['id' => 'service_id'])
			->via('servicesRel')
			->inverseOf('shops');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getStocks()
	{
		return $this->hasMany(ShopStock::class, ['shop_id' => 'shop_id'])
			->inverseOf('shop');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getRegionCrosses()
	{
		return $this->hasMany(RegionCrosses::class, ['shop_id' => 'shop_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getRegion()
	{
		return $this->hasOne(Region::class, ['region_id' => 'region_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getTimeFrom()
	{
		return $this->hasOne(ShopTime::class, ['time_id' => 'shop_line_time_id_from']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getTimeTo()
	{
		return $this->hasOne(ShopTime::class, ['time_id' => 'shop_line_time_id_to']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getTimeWeekendFrom()
	{
		return $this->hasOne(ShopTime::class, ['time_id' => 'shop_line_time_we_id_from']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getTimeWeekendTo()
	{
		return $this->hasOne(ShopTime::class, ['time_id' => 'shop_line_time_we_id_to']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getNetwork()
	{
		return $this->hasOne(ShopNetwork::class, ['network_id' => 'network_id'])
			->inverseOf('shops');
	}

	public function getScheduleByWeekDays()
	{

		$data = [];

		$days = [
			'пн' => false,
			'вт' => false,
			'ср' => false,
			'чт' => false,
			'пт' => false,
			'сб' => true,
			'вс' => true,
		];

		foreach ($days as $day => $isWeekend) {

			$timeFrom = $isWeekend ? $this->timeWeekendFrom : $this->timeFrom;
			$timeTo = $isWeekend ? $this->timeWeekendTo : $this->timeTo;

			$work = $timeFrom !== null && $timeTo !== null;

			$data[] = [
				'dayOfWeek' => $day,
				'isWorking' => $work,
				'startHour' => $work ? $timeFrom->hours : null,
				'startMinute' => $work ? $timeFrom->minutes : null,
				'endHour' => $work ? $timeTo->hours : null,
				'endMinute' => $work ? $timeTo->minutes : null,
			];
		}

		return $data;
	}

	/**
	 * Get ShopTimeModel id for $day, from 0 to 6, 0 = Sunday
	 * @param int $dayNumber
	 * @return int|null
	 */
	public function getWeekDayTimeIdFromByDay(int $dayNumber): ?int
	{
		return ($t = (int)($this->{"wh{$dayNumber}_time_from"} ?? 0)) > 0 ? $t : null;
	}

	/**
	 * Get ShopTimeModel id for $day, from 0 to 6, 0 = Sunday
	 * @param int $dayNumber
	 * @return int|null
	 */
	public function getWeekDayTimeIdToByDay(int $dayNumber): ?int
	{
		return ($t = (int)($this->{"wh{$dayNumber}_time_to"} ?? 0)) > 0 ? $t : null;
	}

	public function getScheduleByWeekDaysV2(): array
	{

		$timeOptions = ShopTime::getTimeOptions();

		$data = [];

		$days = [
			'вс' => true,
			'пн' => false,
			'вт' => false,
			'ср' => false,
			'чт' => false,
			'пт' => false,
			'сб' => true,
		];

		foreach (array_keys($days) as $day => $dayTxt) {
			$dayData = [
				'label' => $dayTxt,
				'from' => $timeOptions[$this->getWeekDayTimeIdFromByDay($day)] ?? null,
				'to' => $timeOptions[$this->getWeekDayTimeIdToByDay($day)] ?? null,
			];
			$data[$day] = $dayData;
		}

		return $data;
	}

	public function getWorkingTimeSchedule(): array
	{
		static $schedule = [];
		if (!isset($schedule[$this->getId()])) {
			$schedule[$this->getId()] = [];
			$weekDays = [
				1 => 'Пн',
				2 => 'Вт',
				3 => 'Ср',
				4 => 'Чт',
				5 => 'Пт',
				6 => 'Сб',
				0 => 'Вс',
			];
			$timeOptions = ShopTime::getTimeOptions();
			$days = [];
			foreach ($weekDays as $dayNumber => $dayLabel) {
				$from = $timeOptions[$this->getWeekDayTimeIdFromByDay($dayNumber)] ?? null;
				$to = $timeOptions[$this->getWeekDayTimeIdToByDay($dayNumber)] ?? null;
				if (null === $from || null === $to) {
					continue;
				}
				$days[$dayLabel] = sprintf('с %s до %s', $from->getText(), $to->getText());
			}
			$schedule[$this->getId()] = $days;
		}
		return $schedule[$this->getId()];
	}

	public function getWorkingTimeScheduleShort(): array
	{
		static $schedule = [];
		if (!isset($schedule[$this->getId()])) {
			$schedule[$this->getId()] = [];
			$collapseFunction = static function (array $range, string $hours, bool $type) {
				$daysRange = [reset($range)];
				if (count($range) > 1) {
					$daysRange[] = end($range);
				}
				return [
					'weekend' => $type,
					'label' => sprintf('%s: %s', implode('-', $daysRange), $hours),
				];
			};
			$currentRange = [];
			$prevDayHours = null;
			$prevDayType = null;
			foreach ($this->getWorkingTimeSchedule() as $dayLabel => $dayHours) {
				$currentDayType = in_array($dayLabel, ['Сб', 'Вс']);
				if ((null !== $prevDayHours && $dayHours !== $prevDayHours)
					|| ($prevDayType !== null && ($currentDayType !== $prevDayType))) {
					$schedule[$this->getId()][] = $collapseFunction($currentRange, $prevDayHours, $prevDayType);
					$currentRange = [];
				}
				$prevDayType = $currentDayType;
				$prevDayHours = $dayHours;
				$currentRange[] = $dayLabel;
			}
			if ($currentRange !== []) {
				$schedule[$this->getId()][] = $collapseFunction($currentRange, $prevDayHours, $prevDayType);
			}
		}
		return $schedule[$this->getId()];
	}

	/**
	 * @return array
	 */
	public function getSchedule(): array
	{

		$replaceFunc = static function ($time) {
			return preg_replace('/^0/ui', '', $time);
		};

		return [
			'week' => [
				'from' => $this->timeFrom !== null ? $replaceFunc($this->timeFrom->time) : null,
				'to' => $this->timeTo !== null ? $replaceFunc($this->timeTo->time) : null,
			],
			'weekend' => [
				'from' => $this->timeWeekendFrom !== null ? $replaceFunc($this->timeWeekendFrom->time) : null,
				'to' => $this->timeWeekendTo !== null ? $replaceFunc($this->timeWeekendTo->time) : null,
			],
		];
	}

	public function fields()
	{

		$fields = [
			'id',

			'regionId' => static function (self $model) {
				return $model->region_id;
			},

			'networkId' => static function (self $model) {
				return $model->network_id;
			},

			'groupId',

			'zoneId' => static function (self $model) {
				return $model->zone_id;
			},

			'name' => 'title',
			'title',
			'titleShort',
			'slug',

			'address',
			'services' => static function (self $model) {
				return $model->getServicesDescription();
			},

			'workingHours' => static function (self $model) {
				return $model->working_hours;
			},

			'workingTimeSchedule' => 'workingTimeScheduleShort',

			'geoPosition' => static function (self $model) {

				$position = $model->getGeoPosition();
				return $position instanceof GeoPosition ? [$position->getLat(), $position->getLng()] : null;
			},

			'color' => static function (self $model) {
				return $model->metro_color === 'obl' ? null : $model->metro_color;
			},

			'creditCards' => static function (self $model) {
				return $model->getIsPaymentByCards();
			},

			'message' => static function (self $model) {
				return trim($model->message);
			},

			'message2' => static function (self $model) {
				return trim($model->message2);
			},
		];

		return $fields;
	}

	public function getIsPaymentByCards(): bool
	{
		return (int)$this->credit_cards === static::HAS_CARD_PAYMENT;
	}

	/**
	 * @inheritdoc
	 */
	public function extraFields()
	{

		$fields = parent::extraFields();

		$fields[] = 'network';
		$fields[] = 'schedule';

		$fields['groupBy'] = static function (self $model) {
			// Если в москве, группируем по расположению
			if (Yii::$app->region->current->getIsRegionInMoscowGroup()) {
				return !$model->getIsInMoscowPlaced() ? 'Область' : 'Город';
			}
			return null;
		};

		return $fields;
	}

	/**
	 * @return bool
	 */
	public function getIsInMoscowPlaced(): bool
	{
		return $this->metro_color !== 'obl';
	}

	public function getId(): int
	{
		return (int)$this->shop_id;
	}

	public function getTitle()
	{
		return preg_replace('/(Магазин)/u', 'Склад', $this->long_name);
	}

	public function getTitleShort()
	{
		return preg_replace('/(Магазин)/u', 'Склад', $this->short_name);
	}

	public function getSlug()
	{
		return $this->url;
	}

	public function getGroupId()
	{
		return $this->shopgroup_id;
	}

	public function getServicesDescription()
	{
		return trim($this->service_desc);
	}

	public function getEmail()
	{
		return $this->admin_email;
	}

	/**
	 * @return GeoPosition|null
	 */
	public function getGeoPosition(): ?GeoPosition
	{
		$pos = StringHelper::explode($this->map_coords, ',', true, true);
		if (count($pos) !== 2) {
			return null;
		}
		return new GeoPosition((float)$pos[0], (float)$pos[1]);
	}
}
