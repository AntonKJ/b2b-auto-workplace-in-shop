<?php

namespace common\models;

/**
 * This is the model class for table "{{%order_types_stock}}".
 *
 * @property integer $id
 * @property integer $order_type_group_id
 * @property string $item_idx
 * @property integer $amount
 */
class OrderTypeStock extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%order_type_stock}}';
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

	public function fields()
	{
		return [
			'total' => 'amount',
		];
	}

	public function getEntityId()
	{
		return $this->item_idx;
	}

	/**
	 * @inheritdoc
	 * @return \common\models\query\OrderTypeStockQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \common\models\query\OrderTypeStockQuery(get_called_class());
	}
}
