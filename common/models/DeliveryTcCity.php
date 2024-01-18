<?php

namespace common\models;

use common\models\query\DeliveryTcCityQuery;

/**
 * This is the model class for table "{{%tc_delivery}}".
 *
 * @property integer $id
 * @property string $title
 *
 */
class DeliveryTcCity extends \yii\db\ActiveRecord
{
	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->tc_delivery_id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id)
	{
		$this->tc_delivery_id = $id;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%tc_delivery}}';
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
		return [
			'id',
			'title' => 'city',
		];
	}

	/**
	 * @return DeliveryTcCityQuery
	 */
	public static function find()
	{
		return new DeliveryTcCityQuery(get_called_class());
	}
}
