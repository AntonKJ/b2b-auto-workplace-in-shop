<?php

namespace common\models;

use common\models\query\ShoppingCartGoodQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shopping_cart_item}}".
 *
 * @property integer $id
 * @property integer $cart_id
 * @property string $entity_type
 * @property string $entity_id
 * @property integer $quantity
 *
 * @property ShoppingCart $cart
 */
class ShoppingCartGood extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shopping_cart_item}}';
	}

	public function getEntityType()
	{
		return $this->entity_type;
	}

	public function getEntityId()
	{
		return $this->entity_id;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['cart_id', 'entity_type', 'entity_id', 'quantity'], 'required'],
			[['cart_id', 'quantity'], 'integer'],
			[['entity_type'], 'string', 'max' => 6],
			[['entity_id'], 'string', 'max' => 50],
			[['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShoppingCart::class, 'targetAttribute' => ['cart_id' => 'id']],
		];
	}

	/**
	 * @return ActiveQuery
	 */
	public function getCart()
	{
		return $this->hasOne(ShoppingCart::class, ['id' => 'cart_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getZonePrice()
	{
		return $this->hasOne(ZonePrice::class, ['item_idx' => 'entity_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getOrderTypeStock()
	{
		return $this->hasOne(OrderTypeStock::class, ['item_idx' => 'entity_id']);
	}

	/**
	 * @inheritdoc
	 * @return ShoppingCartGoodQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new ShoppingCartGoodQuery(static::class);
	}

	public function fields()
	{
		return [
			'id' => 'entity_id',
			'type' => 'entity_type',
			'quantity',
		];
	}

	public function extraFields()
	{
		return [
			'id',
			'cart_id',
		];
	}
}
