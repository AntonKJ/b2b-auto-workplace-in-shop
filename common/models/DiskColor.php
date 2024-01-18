<?php

namespace common\models;

use common\models\query\DiskColorQuery;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%d_model_color}}".
 *
 * @property integer $id
 * @property string $code
 * @property string $title
 * @property string $slug
 * @property integer $status
 * @property integer $sortorder
 * @property string $created_at
 * @property string $updated_at
 */
class DiskColor extends ActiveRecord
{

	public const STATUS_PUBLISHED = 1;
	public const STATUS_DRAFT = 0;

	public $goodCount;

	static public function getStatusOptions()
	{
		return [
			static::STATUS_DRAFT => 'Черновик',
			static::STATUS_PUBLISHED => 'Опубликовано',
		];
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
				'value' => function ($e) {
					return date('Y-m-d H:i:s');
				},
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%d_model_color}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['title'], 'required'],
			[['status', 'sortorder'], 'integer'],
			[['code'], 'string', 'max' => 16],
			[['title', 'slug'], 'string', 'max' => 255],

			[['status'], 'in', 'range' => array_keys(static::getStatusOptions())],

			[['slug'], 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'code' => 'Code',
			'title' => 'Title',
			'slug' => 'Slug',
			'status' => 'Status',
			'sortorder' => 'Sortorder',
			'created_at' => 'Created At',
			'updated_at' => 'Updated At',
		];
	}

	/**
	 * @inheritdoc
	 * @return DiskColorQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new DiskColorQuery(static::class);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getVariations()
	{
		return $this->hasMany(DiskVariation::className(), ['color_id' => 'id'])
			->inverseOf('color');
	}

	public function fields()
	{

		$fields = [
			'id',
			'code',
			'title',
			'slug',
		];

		return $fields;
	}

}
