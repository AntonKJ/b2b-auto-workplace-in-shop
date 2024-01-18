<?php

namespace common\models;

/**
 * This is the model class for table "{{%order_types_delivery_city}}".
 *
 * @property integer $order_type_id
 * @property string $city_id
 *
 */
class DeliveryZoneDeliveryCity extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%dc_2_dz}}';
	}

}
