<?php

namespace common\models;

use common\components\ecommerce\GoodIdentity;
use common\components\ecommerce\models\ShopStock as ShopStockEntity;
use common\interfaces\ShopStockInterface;
use common\models\query\ZonePriceQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_stock}}".
 *
 * @property string $shop_stock_id
 * @property integer $shop_id
 * @property string $item_idx
 * @property integer $amount
 */
class ShopStock extends ActiveRecord implements ShopStockInterface
{

	/**
	 * @var ShopStockEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): ShopStockEntity
	{

		if ($this->_ecommerceEntity === null)
			$this->_ecommerceEntity = new ShopStockEntity(
				(int)$this->shop_id,
				(int)$this->amount,
				new GoodIdentity($this->getGoodId())
			);

		return $this->_ecommerceEntity;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shop_stock}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['shop_stock_id'], 'required'],
			[['shop_stock_id', 'shop_id', 'amount'], 'integer'],
			[['item_idx'], 'string', 'max' => 50],
		];
	}

	public function getGoodId(): string
	{
		return $this->item_idx;
	}

	public function getShopId(): int
	{
		return $this->shop_id;
	}

	public function getAmount(): int
	{
		return $this->amount;
	}

	/**
	 * @inheritdoc
	 * @return query\ShopStockQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new query\ShopStockQuery(static::class);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getShop()
	{
		return $this->hasOne(Shop::className(), ['shop_id' => 'shop_id'])
			->inverseOf('stocks');
	}

	/**
	 * @return ActiveQuery|ZonePriceQuery
	 */
	public function getZonePrice()
	{
		return $this->hasMany(ZonePrice::class, ['item_idx' => 'item_idx']);
	}
}
