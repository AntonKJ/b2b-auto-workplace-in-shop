<?php

namespace common\models;

use common\models\query\MetroQuery;

/**
 * This is the model class for table "{{%metro}}".
 *
 * @property integer $id
 * @property string $title
 *
 */
class Metro extends \yii\db\ActiveRecord
{

	public function getLines()
	{
		return $this->hasMany(MetroLine::class, ['metro_id' => 'id']);
	}

	public function getStations()
	{
		return $this->hasMany(MetroStation::class, ['line_id' => 'id'])
			->via('lines');
	}

	public function getOrderTypeRel()
	{
		return $this->hasOne(OrderTypesMetro::class, ['metro_id' => 'id']);
	}

	public function getOrderType()
	{
		return $this->hasOne(OrderType::class, ['ot_id' => 'order_type_id'])
			->via('orderTypeRel');
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%metro}}';
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

	/**
	 * @return MetroQuery
	 */
	public static function find()
	{
		return new MetroQuery(get_called_class());
	}
}
