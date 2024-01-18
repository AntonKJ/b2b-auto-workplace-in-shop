<?php

namespace common\models;

/**
 * This is the model class for table "{{%auto_models}}".
 *
 * @property string $model_id
 * @property string $prod
 * @property string $model
 * @property integer $ystart
 * @property integer $yend
 * @property string $engine
 * @property string $logo_image
 * @property string $automodel_code_1c
 */
class AutoBrand extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%auto_models}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [];
	}

	/**
	 * @inheritdoc
	 * @return query\AutoBrandQuery
	 */
	public static function find()
	{
		return new \common\models\query\AutoBrandQuery(get_called_class());
	}

	public function getId()
	{
		return $this->slug;
	}

	public function getSlug()
	{
		return $this->brand_slug;
	}

	public function getTitle()
	{
		return $this->prod;
	}

	public function fields()
	{

		$out = [
			'id',
			'slug',
			'title',
		];

		return $out;
	}
}
