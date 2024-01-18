<?php

namespace common\models;

/**
 * This is the model class for table "{{%ou_2_dprod_restrict}}".
 *
 * @property integer $id
 * @property integer $opt_user_id
 * @property integer $d_producer_id
 */
class OptUserDProducerRestrict extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%ou_2_dprod_restrict}}';
	}

	/**
	 * @inheritdoc
	 * @return query\OptUserDProducerRestrictQuery
	 */
	public static function find()
	{
		return new \common\models\query\OptUserDProducerRestrictQuery(get_called_class());
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(OptUser::class, ['id' => 'opt_user_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBrand()
	{
		return $this->hasOne(DiskBrand::class, ['d_producer_id' => 'd_producer_id']);
	}
}
