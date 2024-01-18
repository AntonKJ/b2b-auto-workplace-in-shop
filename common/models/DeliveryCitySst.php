<?php

namespace common\models;

use common\components\ecommerce\models\DeliveryCitySst as DeliveryCitySstEntity;
use common\models\query\DeliveryCitySstQuery;
use domain\interfaces\DeliveryDaysInterface;
use domain\traits\DeliveryDaysTrait;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%delivery_city_sst}}".
 *
 * @property int $deliveryDaysMask
 * @property DeliveryCitySstEntity $ecommerceEntity
 * @property int $id
 * @property string $title
 * @property int $dcsst_id [int(11)]
 * @property int $zone_id [int(11)]
 * @property string $name [varchar(63)]
 * @property int $is_active [int(11)]
 * @property int $delivery_days [int(11)]
 */
class DeliveryCitySst extends ActiveRecord implements DeliveryDaysInterface
{

	use DeliveryDaysTrait;

	public const IS_ACTIVE = 1;

	/**
	 * @var DeliveryCitySstEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): DeliveryCitySstEntity
	{
		if ($this->_ecommerceEntity === null) {
			$this->_ecommerceEntity = new DeliveryCitySstEntity(
				(int)$this->id,
				$this->getTitle(),
				(int)$this->zone_id,
				$this->getDeliveryDaysMask()
			);
		}
		return $this->_ecommerceEntity;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%delivery_city_sst}}';
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->dcsst_id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->name;
	}

	public function fields()
	{
		return [
			'id',
			'zoneId' => 'zone_id',
			'title',
			'delivery' => static function (self $model) {
				return [
					'days' => $model->getDeliveryDays(),
				];
			},
		];
	}

	/**
	 * @return DeliveryCitySstQuery
	 */
	public static function find()
	{
		return new DeliveryCitySstQuery(static::class);
	}

	/**
	 * Возвращает маску для текущего города
	 * @return int
	 */
	public function getDeliveryDaysMask(): int
	{
		return $this->delivery_days === null ? DeliveryDaysInterface::DELIVERY_DAYS_ALL : (int)$this->delivery_days;
	}

}
