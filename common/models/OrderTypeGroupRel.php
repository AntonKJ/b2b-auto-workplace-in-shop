<?php

namespace common\models;

/**
 * This is the model class for table "{{%order_type_group_rel}}".
 *
 * @property integer $id
 * @property integer $order_type_id
 * @property integer $group_id
 */
class OrderTypeGroupRel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_type_group_rel}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_type_id', 'group_id'], 'required'],
            [['order_type_id', 'group_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_type_id' => 'Order Type ID',
            'group_id' => 'Group ID',
        ];
    }
}
