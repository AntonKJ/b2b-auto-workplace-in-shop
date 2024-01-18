<?php

namespace common\models;

use common\components\file\storageStrategy\DiskVariationStorageStrategyDefault;
use common\components\file\storageStrategy\DiskVariationStorageStrategyFace;
use common\components\file\storageStrategy\DiskVariationStorageStrategyHuge;
use common\components\file\storageStrategy\DiskVariationStorageStrategyPreview;
use common\components\file\ThumbnailBehavior;
use common\interfaces\VariationInterface;
use common\models\query\DiskVariationQuery;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%d_model_variation}}".
 *
 * @property integer $id
 * @property integer $model_id
 * @property integer $color_id
 * @property integer $status
 * @property integer $sortorder
 * @property string $created_at
 * @property string $updated_at
 * @property string $slug_img_old
 */
class DiskVariation extends ActiveRecord implements VariationInterface
{

	public const STATUS_PUBLISHED = 1;
	public const STATUS_DRAFT = 0;

	public $goodCount;
	public $sizeCount;

	public static function getStatusOptions()
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
		return '{{%d_model_variation}}';
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
					'targetAttribute' => ['model_id', 'slug'],
				],
			],
			[
				'class' => TimestampBehavior::class,
				'value' => static function ($e) {
					return date('Y-m-d H:i:s');
				},
			],
			'image' => [
				'class' => ThumbnailBehavior::class,
				'defaultThumbnail' => 'default',
				'thumbnails' => [
					'default' => [
						'target' => DiskVariationStorageStrategyDefault::class,
					],
					'preview' => [
						'target' => DiskVariationStorageStrategyPreview::class,
					],
					'huge' => [
						'target' => DiskVariationStorageStrategyHuge::class,
					],
					'face' => [
						'target' => DiskVariationStorageStrategyFace::class,
					],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

			[['model_id'], 'required'],
			[['model_id', 'color_id', 'status', 'sortorder'], 'integer'],

			[['status'], 'in', 'range' => array_keys(static::getStatusOptions())],

			[['slug_img_old'], 'string', 'max' => 255],

			[['model_id', 'color_id'], 'unique', 'targetAttribute' => ['model_id', 'color_id'],
				'message' => 'The combination of Model ID and Color ID has already been taken.'],

			[['title', 'slug'], 'string', 'max' => 255],
			[['slug'], 'unique', 'targetAttribute' => ['model_id', 'slug'],
				'message' => 'The combination of Model ID and Slug has already been taken.'],

			[['sizeCount'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 * @return DiskVariationQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return (new DiskVariationQuery(get_called_class()));
	}

	/**
	 * @return ActiveQuery
	 */
	public function getModel()
	{
		return $this->hasOne(DiskModel::class, ['id' => 'model_id'])
			->inverseOf('variations');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getGoods()
	{
		return $this->hasMany(DiskGood::class, ['variation_id' => 'id'])
			->inverseOf('variation');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getColor()
	{
		return $this->hasOne(DiskColor::class, ['id' => 'color_id'])
			->inverseOf('variations');
	}

	public function getLogo()
	{
		return $this->getThumbnail()->getUrl();
	}

	public function fields()
	{

		$fileds = [
			'id',
			'model_id',
			'color_id',
			'title',
			'slug',
			'logo' => static function ($model) {
				return [
					'default' => $model->getThumbnail('default')->getUrl(),
					'preview' => $model->getThumbnail('preview')->getUrl(),
					'huge' => $model->getThumbnail('huge')->getUrl(),
					'face' => $model->getThumbnail('face')->getUrl(),
				];
			},
		];

		if (null !== $this->sizeCount) {
			$fileds[] = 'sizeCount';
		}

		return $fileds;
	}

	public function extraFields()
	{

		$fields = parent::extraFields();

		$fields[] = 'color';

		$fields['brand_short'] = static function ($model) {
			if ($model->model === null || $model->model->brand === null) {
				return null;
			}
			return $model->model->brand->toArrayShort();
		};

		$fields['model_short'] = static function ($model) {
			if ($model->model === null) {
				return null;
			}
			return $model->model->toArrayShort();
		};

		return $fields;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getTitle()
	{
		$this->title;
	}

}
