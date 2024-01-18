<?php

namespace common\models;

use common\components\ecommerce\models\MetroStation as MetroStationEntity;
use common\interfaces\PoiInterface;
use common\models\query\MetroStationQuery;
use common\models\query\OrderTypeQuery;
use domain\interfaces\DeliveryDaysInterface;
use domain\traits\DeliveryDaysTrait;
use myexample\ecommerce\GeoPosition;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%metro_station}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $hex_color
 * @property integer $sortorder
 *
 * @property OrderTypesMetro $orderTypeRel
 * @property OrderType $orderType
 * @property int $line_id [int(11)]
 * @property string $lat [decimal(11,8)]
 * @property string $lng [decimal(11,8)]
 *
 */
class MetroStation extends ActiveRecord implements DeliveryDaysInterface, PoiInterface
{

	use DeliveryDaysTrait;

	public const POI_TYPE = 'metro';
	public const AREA_RADIUS = 7000; // в метрах

	public $distance;
	public $orderTypeId;

	/**
	 * @var MetroStationEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): MetroStationEntity
	{

		if ($this->_ecommerceEntity === null) {

			$geoPosition = new GeoPosition(...$this->getGeoPosition());

			$this->_ecommerceEntity = new MetroStationEntity(
				(int)$this->id,
				$this->getTitle(),
				$geoPosition,
				$this->getDeliveryDaysMask(),
				$this->distance
			);
		}

		return $this->_ecommerceEntity;
	}

	public function getLine()
	{
		return $this->hasOne(MetroLine::class, ['id' => 'line_id']);
	}

	public function getMetro()
	{
		return $this->hasOne(Metro::class, ['id' => 'metro_id'])
			->via('line');
	}

	public function getOrderTypeRel()
	{
		return $this->hasOne(OrderTypesMetro::class, ['metro_id' => 'metro_id'])
			->via('line');
	}

	/**
	 * @return \yii\db\ActiveQuery|OrderTypeQuery
	 */
	public function getOrderType()
	{
		return $this->hasOne(OrderType::class, ['ot_id' => 'order_type_id'])
			->via('orderTypeRel');
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%metro_station}}';
	}

	public function fields()
	{

		return [
			'id',
			'type' => 'poiType',
			'lineId' => 'line_id',
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
	 * @return MetroStationQuery
	 */
	public static function find()
	{
		return new MetroStationQuery(static::class);
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getPoiType()
	{
		return static::POI_TYPE;
	}

	public function getGeoPosition()
	{
		return [$this->lat, $this->lng];
	}

	/**
	 * Возвращает маску рассписания для доставки
	 * @return int
	 */
	public function getDeliveryDaysMask(): int
	{
		return DeliveryDaysInterface::DELIVERY_DAYS_ALL;
	}

	public function getDistance()
	{
		return $this->distance;
	}

	public function getOrderTypeQuery()
	{
		return $this->getOrderType();
	}

	public function getAreaRadius()
	{
		return static::AREA_RADIUS;
	}

}
