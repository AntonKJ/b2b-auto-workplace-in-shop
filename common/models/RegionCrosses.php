<?php

namespace common\models;

use common\models\query\RegionCrossesQuery;

/**
 * This is the model class for table "{{%region_crosses}}".
 *
 * @property integer $region_id
 * @property integer $shop_id
 */
class RegionCrosses extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%region_crosses}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['region_id', 'shop_id'], 'integer'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'region_id' => 'Region ID',
			'shop_id' => 'Shop ID',
		];
	}

	/**
	 * @inheritdoc
	 * @return RegionCrossesQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new RegionCrossesQuery(get_called_class());
	}
}
