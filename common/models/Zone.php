<?php

namespace common\models;

use common\models\query\ZoneQuery;

/**
 * This is the model class for table "{{%zones}}".
 */
class Zone extends \yii\db\ActiveRecord
{

	/**
	 * @return integer[]
	 */
	public static function getZoneIds()
	{
		return static::find()
			->select('[[zone_id]]')
			->cache(0)
			->column();
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%zones}}';
	}

	/**
	 * @inheritdoc
	 * @return query\ZoneQuery
	 */
	public static function find()
	{
		return new ZoneQuery(static::class);
	}

}
