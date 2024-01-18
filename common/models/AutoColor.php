<?php

namespace common\models;

use common\models\query\AutoColorQuery;

/**
 * This is the model class for table "{{%car_colors}}".
 *
 * @property int $id
 * @property string $title
 * @property string $color
 * @property integer $category_id
 */
class AutoColor extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%car_colors}}';
	}

	/**
	 * @inheritdoc
	 * @return query\AutoColorQuery
	 */
	public static function find()
	{
		return new AutoColorQuery(static::class);
	}

	public function getTitle()
	{
		return $this->colorname;
	}

	public function getColor()
	{
		return $this->colorcode;
	}

	public function fields()
	{
		return [
			'id',
			'title',
			'color',
		];
	}
}
