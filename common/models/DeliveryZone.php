<?php

namespace common\models;

use common\components\ecommerce\models\DeliveryZone as DeliveryZoneEntity;
use common\models\query\DeliveryCityQuery;
use common\models\query\DeliveryZoneQuery;
use common\models\query\OrderTypeQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%delivery_zone}}".
 *
 * @property int $id [int(11)]
 * @property string $Name [varchar(50)]
 * @property string $Email [varchar(63)]
 * @property string $Coords
 * @property string $Color [varchar(6)]
 * @property int $Store [int(11)]
 * @property int $Price [int(11)]
 * @property bool $is_published [tinyint(1) unsigned]
 * @property int $order_type_id [int(11)]
 * @property string $delivery_area [geometry]
 *
 * @property OrderType $orderType
 */
class DeliveryZone extends ActiveRecord
{

	/**
	 * @var DeliveryZoneEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): DeliveryZoneEntity
	{
		if ($this->_ecommerceEntity === null) {
			$this->_ecommerceEntity = new DeliveryZoneEntity(
				(int)$this->id,
				(int)$this->order_type_id,
				$this->getDeliveryAreaArray()
			);
		}
		return $this->_ecommerceEntity;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%delivery_zone}}';
	}

	public function attributeLabels()
	{
		return [
			'title' => 'Наименование',
			'email' => 'Адрес эл. почты',
			'color' => 'Цвет области',
			'is_published' => 'Статус',
			'order_type_id' => 'Тип заказа',
			'delivery_days' => 'Рассписание',
		];
	}

	public function getId(): int
	{
		return (int)$this->id;
	}

	public function getTitle()
	{
		return $this->Name;
	}

	public function getEmail()
	{
		return $this->Email;
	}

	public function getColor()
	{
		return mb_strtolower($this->Color);
	}

	/**
	 * @return null|int
	 */
	public function getDeliveryDays()
	{
		return $this->delivery_days;
	}

	/**
	 * @return OrderTypeQuery|ActiveQuery
	 */
	public function getOrderType()
	{
		return $this->hasOne(OrderType::class, ['ot_id' => 'order_type_id']);
	}

	public function getCitiesRel()
	{
		return $this->hasMany(DeliveryZoneDeliveryCity::class, ['delivery_zone_id' => 'id']);
	}

	/**
	 * @return DeliveryCityQuery|ActiveQuery
	 */
	public function getCities()
	{
		return $this->hasMany(DeliveryCity::class, ['id' => 'delivery_city_id'])
			->via('citiesRel');
	}

	/**
	 * @return DeliveryZoneQuery|ActiveQuery
	 */
	public static function find()
	{
		return new DeliveryZoneQuery(static::class);
	}

	public function getDeliveryAreaArray($refresh = false)
	{
		static $data = [];
		/** @noinspection NotOptimalIfConditionsInspection */
		if (!isset($data[$this->id]) || $refresh) {
			$data[$this->id] = !empty($this->delivery_area) ? json_decode($this->delivery_area, true) : null;
		}
		return $data[$this->id];
	}

	public function fields()
	{
		return [
			'id',
			'color',
			'geometry' => 'deliveryAreaArray',
		];
	}

	public function extraFields()
	{
		return [
			'cities',
		];
	}
}
