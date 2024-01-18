<?php

namespace common\models;

use common\components\ecommerce\models\DeliveryCity as DeliveryCityEntity;
use common\interfaces\PoiInterface;
use common\models\query\DeliveryCityQuery;
use common\models\query\OrderTypeQuery;
use domain\interfaces\DeliveryDaysInterface;
use domain\traits\DeliveryDaysTrait;
use myexample\ecommerce\GeoPosition;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%delivery_city}}".
 *
 * @property integer $id
 * @property string $title
 * @property Region|null $deliveryDaysRegion
 * @property int $Zone_id [int(11)]
 * @property string $City [varchar(50)]
 * @property string $Coords
 * @property int $Price [int(11)]
 * @property int $delivery_price_id [int(11)]
 * @property int $delivery_days [int(11)]
 * @property string $lat [decimal(11,8)]
 * @property string $lng [decimal(11,8)]
 */
class DeliveryCity extends ActiveRecord implements DeliveryDaysInterface, PoiInterface
{

	use DeliveryDaysTrait;

	public const POI_TYPE = 'city';
	public const AREA_RADIUS = 12000; // в метрах

	public $distance;

	/**
	 * @var DeliveryCityEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): DeliveryCityEntity
	{
		if ($this->_ecommerceEntity === null) {
			$geoPosition = new GeoPosition(...$this->getGeoPosition());
			$this->_ecommerceEntity = new DeliveryCityEntity(
				(int)$this->id,
				$this->getTitle(),
				$geoPosition,
				$this->getDeliveryDaysMask(),
				$this->distance
			);
		}
		return $this->_ecommerceEntity;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	public function getZonesRel()
	{
		return $this->hasMany(DeliveryZoneDeliveryCity::class, ['delivery_city_id' => 'id']);
	}

	public function getZones()
	{
		return $this->hasMany(DeliveryZone::class, ['id' => 'delivery_zone_id'])
			->via('zonesRel');
	}

	/**
	 * @return ActiveQuery|OrderTypeQuery
	 */
	public function getOrderTypes()
	{
		return $this
			->hasMany(OrderType::class, ['ot_id' => 'order_type_id'])
			->via('zones')
			->inverseOf('cities');
	}

	public function behaviors()
	{
		return [
			'typecast' => [
				'class' => AttributeTypecastBehavior::class,
				'attributeTypes' => [
					'lat' => AttributeTypecastBehavior::TYPE_FLOAT,
					'lng' => AttributeTypecastBehavior::TYPE_FLOAT,
					'distance' => AttributeTypecastBehavior::TYPE_FLOAT,
				],
				'typecastAfterValidate' => false,
				'typecastAfterFind' => true,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%delivery_city}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'title' => 'Наименование',
		];
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->City;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title)
	{
		$this->City = $title;
	}

	public function getGeoPosition()
	{
		return [(float)$this->lat, (float)$this->lng];
	}

	public function fields()
	{
		return [
			'id',
			'type' => 'poiType',
			'title',
			'geoPosition',
			'areaRadius',
			'delivery' => static function (self $model) {
				return [
					'days' => $model->getDeliveryDays(),
				];
			},
		];
	}

	/**
	 * @return DeliveryCityQuery
	 */
	public static function find()
	{
		return new DeliveryCityQuery(static::class);
	}

	/**
	 * Возвращает маску для текущего города
	 * @return int
	 */
	public function getDeliveryDaysMask(): int
	{
		return $this->delivery_days === null ? DeliveryDaysInterface::DELIVERY_DAYS_ALL : (int)$this->delivery_days;
	}

	public function getPoiType()
	{
		return static::POI_TYPE;
	}

	public function getDistance()
	{
		return $this->distance;
	}

	public function getOrderTypeQuery()
	{
		return $this->getOrderTypes();
	}

	public function getAreaRadius()
	{
		return ($r = (int)$this->delivery_area_radius) === 0 ? static::AREA_RADIUS : $r;
	}

}
