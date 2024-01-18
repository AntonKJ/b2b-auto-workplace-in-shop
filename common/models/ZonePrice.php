<?php

namespace common\models;

/**
 * This is the model class for table "{{%zone_price}}".
 *
 * @property integer $zone_price_id
 * @property integer $zone_id
 * @property string $item_idx
 * @property integer $price
 * @property integer $sale
 * @property integer $offer
 * @property integer $replacement
 * @property integer $preorder
 * @property integer $special
 * @property integer $local_amount
 * @property string $action
 */
class ZonePrice extends \yii\db\ActiveRecord
{

	const OFFER = 1;
	const SALE = 1;
	const PREORDER = 1;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%zone_price}}';
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
	 * @inheritdoc
	 * @return \common\models\query\ZonePriceQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \common\models\query\ZonePriceQuery(get_called_class());
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{

		$fields = [
			'zoneId' => 'zone_id',
			'price' => function ($model) {
				return (float)$model->price;
			},
			'isPreorder',
			'isOffer',
			'isSale',

		];

		return $fields;
	}

	/**
	 * @return string
	 */
	public function getEntityId()
	{
		return $this->item_idx;
	}

	/**
	 * @return bool
	 */
	public function getIsPreorder()
	{
		return (int)$this->preorder == static::PREORDER;
	}

	/**
	 * @return bool
	 */
	public function getIsOffer()
	{
		return (int)$this->offer == static::OFFER;
	}

	/**
	 * @return bool
	 */
	public function getIsSale()
	{
		return (int)$this->sale == static::SALE;
	}

}
