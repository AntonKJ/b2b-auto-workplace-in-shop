<?php

namespace common\models;

/**
 * This is the model class for table "{{%shopping_cart_token}}".
 *
 * @property integer $cart_id
 * @property string $token
 *
 * @property ShoppingCart $cart
 */
class ShoppingCartToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shopping_cart_token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cart_id', 'token'], 'required'],
            [['cart_id'], 'integer'],
            [['token'], 'string', 'max' => 32],
            [['token'], 'unique'],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShoppingCart::class, 'targetAttribute' => ['cart_id' => 'id']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(ShoppingCart::class, ['id' => 'cart_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ShoppingCartTokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ShoppingCartTokenQuery(get_called_class());
    }
}
