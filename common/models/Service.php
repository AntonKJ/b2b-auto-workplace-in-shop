<?php

namespace common\models;

/**
 * This is the model class for table "{{%services}}".
 */
class Service extends \yii\db\ActiveRecord
{

	/**
	 * Шиномонтаж
	 * @var
	 */
	CONST TYRE_MOUNT_SERVICE = 12;

	/**
	 * Хранение
	 * @var
	 */
	CONST SEASON_STORAGE_SERVICE = 2;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%services}}';
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getShopsRel()
	{
		return $this->hasMany(ShopService::class, ['service_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getShops()
	{
		return $this->hasMany(Shop::class, ['shop_id' => 'shop_id'])
			->via('shopsRel')
			->inverseOf('services');
	}

}
