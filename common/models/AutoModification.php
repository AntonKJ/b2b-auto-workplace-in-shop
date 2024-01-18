<?php

namespace common\models;

use common\models\query\AutoModificationQuery;

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
 * @property string $modification_slug [varchar(255)]
 *
 * @property string $rangeText
 *
 * @property AutoImage[] $images
 * @property AutoColor[] $colors
 */
class AutoModification extends \yii\db\ActiveRecord
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

	public function getModel()
	{
		return $this->hasOne(AutoModel::class, ['modification_slug' => 'modification_slug']);
	}

	public function getBrand()
	{
		return $this->hasOne(AutoBrand::class, ['modification_slug' => 'modification_slug']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getImages()
	{
		return $this->hasMany(AutoImage::class, ['automodel_code_1c' => 'automodel_code_1c'])
			->inverseOf('modification');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getColors()
	{
		return $this->hasMany(AutoColor::class, ['id' => 'color_id'])
			->via('images');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTyreRel()
	{
		return $this->hasOne(AutoTyre::class, ['automodel_code_1c' => 'automodel_code_1c']);
	}

	/**
	 * @inheritdoc
	 * @return query\AutoModificationQuery
	 */
	public static function find()
	{
		return new AutoModificationQuery(static::class);
	}

	public function getId()
	{
		return $this->slug;
	}

	public function getBrandId()
	{
		return $this->brand_slug;
	}

	public function getModelId()
	{
		return $this->model_slug;
	}

	public function getTitle()
	{
		return trim($this->rangeText);
	}

	public function getTitleFull()
	{
		return trim("{$this->prod} {$this->model} {$this->rangeText}");
	}

	public function getYearStart()
	{
		return ($y = (int)$this->ystart) == 0 || $y == 1000 || $y == 3000 ? null : $y;
	}

	public function getYearEnd()
	{
		return ($y = (int)$this->yend) == 0 || $y == 1000 || $y == 3000 ? null : $y;
	}

	public function getRange()
	{
		return [
			'start' => $this->yearStart,
			'end' => $this->yearEnd,
		];
	}

	/**
	 * @param string $delimiter
	 * @return null|string
	 */
	public function getRangeText($delimiter = ' — ')
	{
		$range = $this->range;

		return null === $range['start'] && null === $range['end'] ? null : trim(implode($delimiter, $this->range));
	}

	public function getSlug()
	{
		return $this->modification_slug;
	}

	/**
	 * @return null|string
	 */
	public function getEngineText()
	{
		$engine = trim($this->engine);
		return !empty($engine) && $engine !== '*' ? $engine : null;
	}

	public function fields()
	{

		$out = [
			'id',
			'slug',
			'brandId',
			'modelId',
			'title',
			'titleFull',
			'range',
			'rangeText',
			'engine' => 'engineText',
		];

		return $out;
	}
}
