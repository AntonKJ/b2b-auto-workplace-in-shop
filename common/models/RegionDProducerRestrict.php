<?php

namespace common\models;

use common\models\query\DiskBrandQuery;
use common\models\query\RegionDProducerRestrictQuery;
use common\models\query\RegionQuery;

/**
 * This is the model class for table "{{%regions_dprod_restrict}}".
 *
 * @property integer $id
 * @property integer $region_id
 * @property integer $producer_id
 */
class RegionDProducerRestrict extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%regions_dprod_restrict}}';
	}

	/**
	 * @inheritdoc
	 * @return RegionDProducerRestrictQuery
	 */
	public static function find()
	{
		return new RegionDProducerRestrictQuery(static::class);
	}

	/**
	 * @return \yii\db\ActiveQuery|RegionQuery
	 */
	public function getRegion()
	{
		return $this->hasOne(Region::class, ['region_id' => 'region_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|DiskBrandQuery
	 */
	public function getBrand()
	{
		return $this->hasOne(DiskBrand::class, ['id' => 'd_producer_id']);
	}
}
