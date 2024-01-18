<?php

namespace common\models;

use common\models\query\RegionProducerRestrictQuery;
use common\models\query\RegionQuery;
use common\models\query\TyreBrandQuery;

/**
 * This is the model class for table "{{%regions_prod_restrict}}".
 *
 * @property integer $id
 * @property integer $region_id
 * @property integer $producer_id
 */
class RegionProducerRestrict extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%regions_prod_restrict}}';
	}

	/**
	 * @inheritdoc
	 * @return RegionProducerRestrictQuery
	 */
	public static function find()
	{
		return new RegionProducerRestrictQuery(static::class);
	}

	/**
	 * @return \yii\db\ActiveQuery|RegionQuery
	 */
	public function getRegion()
	{
		return $this->hasOne(Region::class, ['region_id' => 'region_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|TyreBrandQuery
	 */
	public function getBrand()
	{
		return $this->hasOne(TyreBrand::class, ['id' => 'producer_id']);
	}
}
