<?php

namespace common\models;

/**
 * This is the model class for table "{{%catalog_index}}".
 *
 * @property integer $id
 * @property string $entity_type
 * @property integer $entity_id
 * @property string $words
 * @property integer $brand_id
 * @property integer $model_id
 */
class CatalogIndex extends \yii\db\ActiveRecord
{

	const ENTITY_TYPE_TYRE = 'tyre';
	const ENTITY_TYPE_DISK = 'disk';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%catalog_index}}';
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
}
