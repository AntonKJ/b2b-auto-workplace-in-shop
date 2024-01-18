<?php

namespace common\models;

use common\models\query\ShopQuery;
use common\models\query\ZonePriceQuery;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%shop_stock_total}}".
 *
 * @property int $shop_stock_total_id
 * @property string $item_idx
 * @property int $shop_id
 * @property int $total
 * @property int $total_10k
 */
class ShopStockTotal extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shop_stock_total}}';
	}

	/**
	 * @inheritdoc
	 * @return query\ShopStockTotalQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new query\ShopStockTotalQuery(static::class);
	}

	/**
	 * @return ActiveQuery|ShopQuery
	 */
	public function getShop()
	{
		return $this->hasOne(Shop::class, ['shop_id' => 'shop_id']);
	}

	/**
	 * @return ActiveQuery|ZonePriceQuery
	 */
	public function getZonePrice()
	{
		return $this->hasMany(ZonePrice::class, ['item_idx' => 'item_idx']);
	}
}
