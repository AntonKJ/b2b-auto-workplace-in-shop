<?php

namespace common\models;

/**
 * This is the model class for table "{{%order_types_metro}}".
 *
 * @property integer $order_type_id
 * @property integer $metro_id
 *
 */
class OrderTypesMetro extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%order_types_metro}}';
	}

}
