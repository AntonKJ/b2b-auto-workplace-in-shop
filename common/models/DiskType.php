<?php

namespace common\models;

use common\models\query\DiskTypeQuery;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%d_model_type}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $slug
 * @property integer $status
 * @property integer $sortorder
 * @property string $created_at
 * @property string $updated_at
 */
class DiskType extends ActiveRecord
{

	public const STATUS_PUBLISHED = 1;
	public const STATUS_DRAFT = 0;

	public $goodCount;

	public static function getStatusOptions(): array
	{
		return [
			static::STATUS_DRAFT => 'Черновик',
			static::STATUS_PUBLISHED => 'Опубликовано',
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%d_model_type}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			[
				'class' => SluggableBehavior::class,
				'attribute' => 'title',
				'slugAttribute' => 'slug',
				'immutable' => true,
				'ensureUnique' => true,
			],
			[
				'class' => TimestampBehavior::class,
				'value' => static function ($e) {
					return date('Y-m-d H:i:s');
				},
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['title'], 'required'],
			[['status', 'sortorder'], 'integer'],
			[['status'], 'in', 'range' => array_keys(static::getStatusOptions())],
			[['title', 'slug'], 'string', 'max' => 255],
			[['slug'], 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 * @return DiskTypeQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new DiskTypeQuery(static::class);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getModels()
	{
		return $this->hasMany(DiskModel::class, ['type_id' => 'id'])
			->inverseOf('type');
	}

}
