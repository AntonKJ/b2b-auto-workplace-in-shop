<?php

namespace common\models;

use common\components\ecommerce\models\OrderType as OrderTypeEntity;
use common\components\deliverySchedule\DeliveryScheduleEvening;
use common\components\deliverySchedule\DeliveryScheduleInterface;
use common\components\deliverySchedule\DeliveryScheduleMorning;
use common\interfaces\OrderTypeInterface;
use common\models\query\DeliveryCityQuery;
use common\models\query\OrderTypeQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%order_types}}".
 *
 * @property integer $ot_id
 * @property string $name
 * @property integer $from_shop_id
 * @property integer $ord_num
 * @property integer $days
 * @property int $id
 * @property mixed $deliveryZones
 * @property bool $isCategoryRussia
 * @property mixed $metro
 * @property bool $isCategoryRegion
 * @property null|int $fromShopId
 * @property ActiveQuery $groupsRel
 * @property bool $isCategoryCity
 * @property mixed $categoryText
 * @property mixed $title
 * @property ActiveQuery $groups
 * @property mixed $citiesRel
 * @property DeliveryCityQuery|ActiveQuery $cities
 * @property mixed $metroRel
 * @property bool $isCategoryRussiaTc
 * @property string $category
 * @property integer $delivery_schedule_id
 * @property integer $allowed_paytypes
 * @property array|DeliveryScheduleInterface[] $deliverySchedule
 * @property string $region_area [geometry]
 *
 */
class OrderType extends ActiveRecord implements OrderTypeInterface
{

	public const ORDER_TYPE_PICKUP = 1;
	public const ORDER_TYPE_CITY = 2;
	public const ORDER_TYPE_RUSSIA = 6;
	public const ORDER_TYPE_RUSSIA_COURIER = 21;
	public const ORDER_TYPE_MO_NORD = 3;
	public const ORDER_TYPE_MO_WEST = 4;
	public const ORDER_TYPE_MO_EAST = 5;
	public const ORDER_TYPE_MO_SOUTH = 7;

	public const ORDER_TYPE_MO_GROUP = [
		self::ORDER_TYPE_MO_NORD,
		self::ORDER_TYPE_MO_WEST,
		self::ORDER_TYPE_MO_EAST,
		self::ORDER_TYPE_MO_SOUTH,
	];

	public const CATEGORY_PICKUP = 'pickup';
	public const CATEGORY_CITY = 'city';
	public const CATEGORY_REGION = 'region';
	public const CATEGORY_RUSSIA = 'russia';
	public const CATEGORY_RUSSIA_TC = 'russia_tc';

	/**
	 * @var OrderTypeEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): OrderTypeEntity
	{
		if ($this->_ecommerceEntity === null) {
			$this->_ecommerceEntity = new OrderTypeEntity(
				$this->getId(),
				$this->getCategory(),
				$this->getFromShopId(),
				$this->getTitle(),
				$this->getDays(),
				(int)$this->delivery_schedule_id,
				(int)$this->allowed_paytypes,
				(int)$this->nextday_time
			);
		}
		return $this->_ecommerceEntity;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%order_types}}';
	}

	/**
	 * @inheritdoc
	 * @return OrderTypeQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new OrderTypeQuery(static::class);
	}

	/**
	 * @return bool
	 */
	public function getIsCategoryRussia()
	{
		return $this->category == static::CATEGORY_RUSSIA;
	}

	/**
	 * @return bool
	 */
	public function getIsCategoryRussiaTc()
	{
		return $this->category == static::CATEGORY_RUSSIA_TC;
	}

	/**
	 * @return bool
	 */
	public function getIsCategoryCity()
	{
		return $this->category == static::CATEGORY_CITY;
	}

	/**
	 * @return bool
	 */
	public function getIsCategoryRegion()
	{
		return $this->category == static::CATEGORY_REGION;
	}

	/**
	 * @return ActiveQuery
	 */
	public function getGroupsRel()
	{
		return $this->hasMany(OrderTypeGroupRel::class, ['order_type_id' => 'ot_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getGroups()
	{
		return $this->hasMany(OrderTypeGroup::class, ['id' => 'group_id'])
			->via('groupsRel');
	}

	public function getCategoryText()
	{
		return ArrayHelper::getValue(static::getCategoryOptions(), $this->category);
	}

	static public function getCategoryOptions()
	{
		return [
			static::CATEGORY_PICKUP => 'Самовывоз',
			static::CATEGORY_CITY => 'Доставка по городу',
			static::CATEGORY_REGION => 'Доставка в область',
			static::CATEGORY_RUSSIA => 'Доставка в регионы',
			static::CATEGORY_RUSSIA_TC => 'Доставка транспортной компанией',
		];
	}

	public function getMetroRel()
	{
		return $this->hasOne(OrderTypesMetro::class, ['order_type_id' => 'ot_id']);
	}

	public function getMetro()
	{
		return $this->hasOne(Metro::class, ['id' => 'metro_id'])
			->via('metroRel');
	}

	public function getDeliveryZones()
	{
		return $this->hasMany(DeliveryZone::class, ['order_type_id' => 'ot_id']);
	}

	public function getCitiesRel()
	{
		return $this->hasMany(DeliveryZoneDeliveryCity::class, ['delivery_zone_id' => 'id'])
			->via('deliveryZones');
	}

	/**
	 * @return ActiveQuery|DeliveryCityQuery
	 */
	public function getCities()
	{
		return $this->hasMany(DeliveryCity::class, ['id' => 'delivery_city_id'])
			->via('citiesRel');
	}

	public function getId()
	{
		return $this->ot_id;
	}

	public function setId($id)
	{

		$this->ot_id = $id;
		return $this;
	}

	public function getTitle()
	{
		return trim(rtrim($this->name, '*'));
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['category'], 'in', 'range' => array_keys(static::getCategoryOptions())],
			[['from_shop_id', 'ord_num', 'days'], 'integer'],
			[['name'], 'string', 'max' => 128],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'ot_id' => 'ID',
			'name' => 'Наименование',
			'from_shop_id' => 'Из магазина',
			'ord_num' => 'Порядок сортировки',
			'days' => 'Дней',
			'category' => 'Категория',
			'region_area' => 'Область действия доставки',
		];
	}

	public function getRegionAreaArray($refresh = false)
	{

		static $data = [];
		if (!isset($data[$this->id]) || $refresh) {

			$data[$this->id] = !empty($this->region_area) ? json_decode($this->region_area, true) : '';
		}

		return $data[$this->id];
	}

	public function fields()
	{
		return [
			'id',
			'title',
			'fromShopId',
			'sortorder' => 'ord_num',
			'days',
			'category',
		];
	}

	public function extraFields()
	{
		return [
			'regionArea' => 'regionAreaArray',
			'deliveryZones',
			'deliverySchedule',
		];
	}

	/**
	 * @return int|null
	 */
	public function getFromShopId()
	{
		return ($shopId = (int)$this->from_shop_id) === 0 ? null : $shopId;
	}

	/**
	 * @return int
	 */
	public function getDays(): int
	{
		return (int)$this->days;
	}

	/**
	 * @return string
	 */
	public function getCategory(): string
	{
		return (string)$this->category;
	}

	/**
	 * @return array|DeliveryScheduleInterface[]
	 */
	public function getDeliverySchedule(): array
	{

		static $schedule = [];

		$deliveryScheduleId = (int)$this->delivery_schedule_id;
		if (!isset($schedule[$deliveryScheduleId])) {

			$schedule[$deliveryScheduleId] = [];

			if ($deliveryScheduleId > 0)
				$schedule[$deliveryScheduleId] = [
					new DeliveryScheduleMorning(),
					new DeliveryScheduleEvening(),
				];
		}

		return $schedule[$deliveryScheduleId];
	}

}
