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
class AutoModel extends \yii\db\ActiveRecord
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
	 * @return \common\models\query\AutoModelQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \common\models\query\AutoModelQuery(get_called_class());
	}

	public function getId() {
		return $this->slug;
	}

	public function getBrandId() {
		return $this->brand_slug;
	}

	public function getSlug() {
		return $this->model_slug;
	}

	public function getTitle() {
		return $this->model;
	}

	public function fields()
	{

		$out = [
			'id',
			'slug',
			'brandId',
			'title',
		];

		return $out;
	}
}
