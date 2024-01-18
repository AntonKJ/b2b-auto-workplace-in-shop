<?php

namespace common\models;

/**
 * This is the model class for table "{{%auto_disks}}".
 *
 * @property string $disk_id
 * @property string $auto_model_id
 * @property string $automodel_code_1c
 */
class AutoDisk extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%auto_disks}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['disk_id', 'auto_model_id', 'automodel_code_1c'], 'string', 'max' => 50],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'disk_id' => 'Disk ID',
			'auto_model_id' => 'Auto Model ID',
			'automodel_code_1c' => 'Automodel Code 1c',
		];
	}

	/**
	 * @inheritdoc
	 * @return \common\models\query\AutoDiskQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \common\models\query\AutoDiskQuery(get_called_class());
	}

	public function getAutoModification()
	{
		return $this->hasOne(AutoModification::class, ['model_id' => 'auto_model_id']);
	}
}
