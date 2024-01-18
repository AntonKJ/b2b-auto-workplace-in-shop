<?php

namespace common\models;

use common\components\file\storageStrategy\DiskBrandStorageStrategyDefault;
use common\components\file\ThumbnailBehavior;
use common\interfaces\BrandInterface;
use common\models\query\DiskBrandQuery;
use common\models\query\OptUserDProducerRestrictQuery;
use common\models\query\RegionDProducerRestrictQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%d_producer}}".
 *
 * @property integer $d_producer_id
 * @property string $code
 * @property string $name
 * @property string $logo
 * @property integer $pos
 * @property string $code_1c
 * @property integer $is_published
 * @property integer $images_version
 */
class DiskBrand extends ActiveRecord implements BrandInterface
{

	public $goodCount;

	public $modelCount;
	public $variationCount;

	public const IS_PUBLISHED = 1;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%d_producer}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'image' => [
				'class' => ThumbnailBehavior::class,
				'defaultThumbnail' => 'default',
				'thumbnails' => [
					'default' => [
						'target' => DiskBrandStorageStrategyDefault::class,
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
			[['code'], 'required'],
			[['pos', 'is_published', 'images_version'], 'integer'],
			[['code', 'name', 'code_1c'], 'string', 'max' => 30],
			[['logo'], 'string', 'max' => 48],
			[['name'], 'unique'],
			//
			[['modelCount', 'variationCount'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 * @return DiskBrandQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new DiskBrandQuery(get_called_class());
	}

	/**
	 * @return ActiveQuery
	 */
	public function getGoods()
	{
		return $this->hasMany(DiskGood::class, ['brand_id' => 'd_producer_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getModels()
	{
		return $this->hasMany(DiskModel::class, ['brand_id' => 'd_producer_id'])
			->inverseOf('brand');
	}

	/**
	 * @return ActiveQuery
	 * @deprecated
	 */
	public function getBrandsHidden()
	{
		return $this->hasMany(OptUserBrandHide::class, ['entity_code' => 'd_producer_id'])
			->andOnCondition(['[[entity_type]]' => OptUserBrandHide::ENTITY_TYPE_DISK]);
	}

	/**
	 * @return OptUserDProducerRestrictQuery|ActiveQuery
	 */
	public function getBrandsRestrict()
	{
		return $this->hasMany(OptUserDProducerRestrict::class, ['d_producer_id' => 'd_producer_id']);
	}

	/**
	 * @return RegionDProducerRestrictQuery|ActiveQuery
	 */
	public function getBrandsRestrictByRegion()
	{
		return $this->hasMany(RegionDProducerRestrict::class, ['d_producer_id' => 'd_producer_id']);
	}

	public function getLogoUrl()
	{
		if (empty($this->logo)) {
			return null;
		}
		return $this->getThumbnail()->getUrl();
	}

	public function getUrl()
	{
		return mb_strtolower($this->code);
	}

	public function getTitle()
	{
		return $this->name;
	}

	public function getId()
	{
		return $this->d_producer_id;
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{

		$fields = [

			'id' => 'd_producer_id',

			'code',
			'name',
			'title' => 'name',

			'logo',
			'logoUrl',

			'url',

			'description' => function () {
				return null;
			},

			'modelCount',
			'variationCount',
		];

		if (null !== $this->modelCount) {
			$fileds[] = 'modelCount';
		}

		if (null !== $this->variationCount) {
			$fileds[] = 'variationCount';
		}

		return $fields;
	}

	public function toArrayShort()
	{
		return $this->toArray([
			'id',

			'code',
			'name',
			'title' => 'name',

			'logoUrl',

			'url',
		]);
	}

	/**
	 * @param bool $refresh
	 * @return array
	 */
	public function getPrepareSearchIndex(bool $refresh = false): array
	{

		// Кешируем сгенерированный индекс
		static $index = [];

		if (!isset($index[$this->d_producer_id]) || $refresh) {

			$words = [];

			$fields = [
				'code', 'name',
			];
			foreach ($fields as $field) {
				$words[] = $this->{$field};
			}

			$words = preg_replace('/\s+/u', ' ', mb_strtolower(trim(implode(' ', $words))));
			$words = preg_split('/[\s,]+/u', $words);

			$index[$this->d_producer_id] = [];
			foreach ($words as $word) {

				$word = trim($word, '.');
				if (!in_array($word, $index[$this->d_producer_id]) && mb_strlen($word) >= 2) {
					$index[$this->d_producer_id][] = $word;
				}
			}

		}

		return $index[$this->d_producer_id];
	}
}
