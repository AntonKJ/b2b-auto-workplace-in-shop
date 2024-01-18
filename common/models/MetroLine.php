<?php

namespace common\models;

use common\models\query\MetroLineQuery;

/**
 * This is the model class for table "{{%metro_line}}".
 *
 * @property integer $id
 * @property integer $metro_id
 * @property string $title
 * @property string $hex_color
 * @property integer $sortorder
 *
 */
class MetroLine extends \yii\db\ActiveRecord
{

	public function getMetro()
	{
		return $this->hasOne(Metro::class, ['id' => 'metro_id']);
	}

	public function getStations()
	{
		return $this->hasMany(MetroStation::class, ['line_id' => 'id']);
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%metro_line}}';
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

	public function fields()
	{

		$fields = parent::fields();

		return $fields;
	}

	public function extraFields()
	{
		return [
			'stations'
		];
	}

	/**
	 * @return MetroLineQuery
	 */
	public static function find()
	{
		return new MetroLineQuery(static::class);
	}
}
