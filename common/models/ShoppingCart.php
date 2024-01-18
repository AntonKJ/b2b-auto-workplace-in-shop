<?php

namespace common\models;

use yii\db\Expression;

/**
 * This is the model class for table "{{%shopping_cart}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $updated_at
 *
 * @property ShoppingCartGood[] $shoppingCartItems
 * @property ShoppingCartToken $shoppingCartToken
 */
class ShoppingCart extends \yii\db\ActiveRecord
{

	public function getCacheTag()
	{
		return "cart-{$this->getId()}";
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shopping_cart}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['user_id'], 'integer'],
			[['updated_at'], 'safe'],
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getGoods()
	{
		return $this->hasMany(ShoppingCartGood::class, ['cart_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getToken()
	{
		return $this->hasOne(ShoppingCartToken::class, ['cart_id' => 'id']);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\query\ShoppingCartQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \common\models\query\ShoppingCartQuery(get_called_class());
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * Обновляем временную метку `updated_at`
	 * @return $this
	 */
	public function touch()
	{
		$this->updateAttributes(['updated_at' => new Expression('CURRENT_TIMESTAMP')]);
		return $this;
	}
}
