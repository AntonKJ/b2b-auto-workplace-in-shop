<?php

namespace common\models;

use common\models\query\CacheVariablesQuery;

/**
 * This is the model class for table "{{%CacheVariables}}".
 *
 * @property string $id
 * @property string $dump
 * @property string $date
 */
class CacheVariables extends \yii\db\ActiveRecord
{

	const ID_GLOBAL = 'global';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%cache_variables}}';
	}

	/**
	 * @return CacheVariablesQuery
	 */
	public static function find()
	{
		return new CacheVariablesQuery(get_called_class());
	}

	public function getDumpAsArray()
	{

		static $cache;
		if (!isset($cache[$this->id])) {

			$cache[$this->id] = [];

			if (!empty($this->dump) && ($dump = unserialize($this->dump)) !== false)
				$cache[$this->id] = $dump;
			elseif (!empty($this->dump) && ($dump = json_decode($this->dump, true)) !== false)
				$cache[$this->id] = $dump;
		}

		return $cache[$this->id];
	}

}
