<?php

namespace common\models;

use common\interfaces\BrandInterface;
use common\models\query\OptUserProducerRestrictQuery;
use common\models\query\RegionProducerRestrictQuery;
use common\models\query\TyreBrandQuery;

/**
 * This is the model class for table "{{%producer}}".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $logo
 * @property integer $Position
 * @property string $distributor
 * @property string $text
 * @property string $wtext
 * @property string $stext
 * @property string $category
 * @property string $inspiration_text
 * @property string $url
 */
class TyreBrand extends \yii\db\ActiveRecord implements BrandInterface
{
	public $goodCount;
	public $modelCount;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%producer}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['code', 'text', 'wtext', 'stext'], 'required'],
			[['Position'], 'integer'],
			[['distributor', 'text', 'wtext', 'stext'], 'string'],
			[['code', 'name'], 'string', 'max' => 30],
			[['logo'], 'string', 'max' => 48],
			[['category'], 'string', 'max' => 1],
			[['inspiration_text'], 'string', 'max' => 255],
			[['url'], 'string', 'max' => 31],
			[['name'], 'unique'],
			[['url'], 'unique'],
			//
			[['modelCount'], 'safe'],
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
			'name' => 'Name',
			'logo' => 'Logo',
			'Position' => 'Position',
			'distributor' => 'Distributor',
			'text' => 'Text',
			'wtext' => 'Wtext',
			'stext' => 'Stext',
			'category' => 'Category',
			'inspiration_text' => 'Inspiration Text',
			'url' => 'Url',
		];
	}

	/**
	 * @inheritdoc
	 * @return TyreBrandQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new TyreBrandQuery(get_called_class());
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getModels()
	{
		return $this->hasMany(TyreModel::class, ['prod_code' => 'code']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getGoods()
	{
		return $this->hasMany(TyreGood::class, ['prod_code' => 'code']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @deprecated
	 */
	public function getBrandsHidden()
	{
		return $this->hasMany(OptUserBrandHide::class, ['entity_code' => 'id'])
			->andOnCondition(['[[entity_type]]' => OptUserBrandHide::ENTITY_TYPE_TYRE]);
	}

	/**
	 * @return OptUserProducerRestrictQuery|\yii\db\ActiveQuery
	 */
	public function getBrandsRestrict()
	{
		return $this->hasMany(OptUserProducerRestrict::class, ['producer_id' => 'id']);
	}

	/**
	 * @return RegionProducerRestrictQuery|\yii\db\ActiveQuery
	 */
	public function getBrandsRestrictByRegion()
	{
		return $this->hasMany(RegionProducerRestrict::class, ['producer_id' => 'id']);
	}

	public function getLogoUrl()
	{

		if (empty($this->logo))
			return null;

		$imagesVersion = (int)$this->images_version;

		return \Yii::$app->media->getStorageUrl(implode('/', [
			'images',
			"{$this->logo}?{$imagesVersion}",
		]));
	}

	public function getTitle()
	{
		return $this->name;
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{

		$fields = [

			'id',
			'code',
			'name' => 'title',
			'title' => 'name',

			'logo',
			'logoUrl',

			'category',

			'text' => function ($model) {
				return trim($model->text);
			},

			'wtext' => function ($model) {
				return trim($model->wtext);
			},

			'stext' => function ($model) {
				return trim($model->stext);
			},

			'url',

		];

		if (null !== $this->modelCount)
			$fields[] = 'modelCount';

		return $fields;
	}

	public function extraFields()
	{

		$fields = parent::extraFields();

		$fields[] = 'models';

		return $fields;
	}

	public function toArrayShort()
	{
		return $this->toArray([
			'id',
			'name',
			'title' => 'name',
			'code',
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

		if (!isset($index[(int)$this->id]) || $refresh) {

			$words = [];

			$fields = ['code', 'name',];
			foreach ($fields as $field)
				$words[] = $this->{$field};

			$words = preg_replace('/\s+/u', ' ', mb_strtolower(trim(implode(' ', $words))));
			$words = preg_split('/[\s,]+/u', $words);

			$index[(int)$this->id] = [];
			foreach ($words as $word) {

				$word = trim($word, '.');
				if (!in_array($word, $index[(int)$this->id]) && mb_strlen($word) >= 2) {

					$index[(int)$this->id][] = $word;
				}
			}
		}

		return $index[(int)$this->id];
	}

}
