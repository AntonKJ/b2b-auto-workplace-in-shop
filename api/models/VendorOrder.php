<?php

namespace api\models;

use api\models\query\VendorOrderQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%vendor_order}}".
 *
 * @property integer $id
 * @property string $vendor вендор
 * @property string $order_id номер резерва (заказа)
 * @property string $status текущий статус
 * @property string $notified_status уведомление статуса
 * @property string $updated_at дата обновления записи
 *
 */
class VendorOrder extends \yii\db\ActiveRecord
{

	public const STATUS_IN_RESERVE = 'Зарезервирован';
	public const STATUS_ASSEMBLE = 'Собирается';
	public const STATUS_CANCELLED = 'Снят с резерва';
	public const STATUS_COMPLETED = 'Отгружен';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%vendor_order}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::class,
				'createdAtAttribute' => false,
				'updatedAtAttribute' => 'updated_at',
				'value' => new Expression('NOW()'),
			],
		];
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

		];
	}

	/**
	 * @return array
	 */
	public function fields()
	{
		return parent::fields();
	}

	/**
	 * @return VendorOrderQuery
	 */
	public static function find()
	{
		return new VendorOrderQuery(static::class);
	}

	public static function createFromReserve()
	{

	}

	public static function mapStatusFrom1C($status)
	{
		return $status;
	}

	public static function mapStatusTo1C($status)
	{
		return $status;
	}

}
