<?php

namespace common\models;

use common\interfaces\BrandModelInterface;
use common\models\query\DiskModelQuery;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%d_model}}".
 *
 * @property integer $id
 * @property integer $brand_id
 * @property string $title
 * @property string $slug
 * @property integer $status
 * @property integer $sortorder
 * @property string $description_short
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */
class DiskModel extends ActiveRecord implements BrandModelInterface
{

	const STATUS_PUBLISHED = 1;
	const STATUS_DRAFT = 0;

	public $goodCount;
	public $sizeCount;

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
				'uniqueValidator' => [
					'targetAttribute' => ['brand_id', 'slug'],
				],
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
		return '{{%d_model}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

			[['brand_id', 'title'], 'required'],
			[['brand_id', 'status', 'sortorder'], 'integer'],

			[['type_id'], 'integer'],

			[['description_short', 'description'], 'string'],
			[['title', 'slug'], 'string', 'max' => 255],

			[['slug'], 'unique', 'targetAttribute' => ['brand_id', 'slug'], 'message' => 'The combination of Brand ID and Slug has already been taken.'],

			[['status'], 'in', 'range' => array_keys(static::getStatusOptions())],

			[['sizeCount'], 'safe'],

		];
	}

	/**
	 * @inheritdoc
	 * @return DiskModelQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new DiskModelQuery(static::class);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getBrand()
	{
		return $this->hasOne(DiskBrand::class, ['d_producer_id' => 'brand_id'])
			->inverseOf('models');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getType()
	{
		return $this->hasOne(DiskType::class, ['id' => 'type_id'])
			->inverseOf('models');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getGoods()
	{
		return $this->hasMany(DiskGood::class, ['model_id' => 'id'])
			->inverseOf('modelRel');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getVariations()
	{
		return $this->hasMany(DiskVariation::class, ['model_id' => 'id'])
			->inverseOf('model');
	}

	public function getId()
	{
		return $this->id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function fields()
	{

		$fileds = [
			'id',
			'brand_id',
			'type_id',
			'title',
			'slug',
		];

		if (null !== $this->sizeCount) {
			$fileds[] = 'sizeCount';
		}

		return $fileds;
	}

	public function extraFields()
	{
		return [
		];
	}

	public function toArrayShort()
	{

		$fields = $this->toArray([

			'id',

			'brand_id',
			'type_id',

			'title',
			'slug',

		]);

		if (null !== $this->sizeCount)
			$fields[] = 'sizeCount';

		return $fields;
	}

	public function getPrepareSearchIndex(): array
	{

		$searchIndex = [];

		$fields = [
			'title', 'slug',
		];
		foreach ($fields as $field) {
			$searchIndex[] = $this->{$field};
		}

		$searchIndex = preg_replace('/\s+/u', ' ', mb_strtolower(trim(implode(' ', $searchIndex))));
		$searchIndex = preg_split('/[\s,]+/u', $searchIndex);

		$out = [];
		foreach ($searchIndex as $itm) {

			$itm = trim($itm, '.');
			if (!in_array($itm, $out) && mb_strlen($itm) >= 2) {
				$out[] = $itm;
			}
		}

		return $out;
	}
}
