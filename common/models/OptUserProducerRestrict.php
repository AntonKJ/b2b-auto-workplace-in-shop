<?php

namespace common\models;

/**
 * This is the model class for table "{{%ou_2_prod_restrict}}".
 *
 * @property integer $id
 * @property integer $opt_user_id
 * @property integer $producer_id
 */
class OptUserProducerRestrict extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%ou_2_prod_restrict}}';
	}

	/**
	 * @inheritdoc
	 * @return query\OptUserProducerRestrictQuery
	 */
	public static function find()
	{
		return new \common\models\query\OptUserProducerRestrictQuery(get_called_class());
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
		return $this->hasOne(TyreBrand::class, ['id' => 'producer_id']);
	}
}
