<?php

namespace common\models;

use common\models\query\AutoTyreQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%auto_tyres}}".
 *
 * @property string $model_id
 * @property string $sz
 * @property string $compatibility
 * @property string $place
 * @property string $width
 * @property string $pr
 * @property string $rad
 * @property string $automodel_code_1c
 */
class AutoTyre extends ActiveRecord
{

	public const COMPATIBILITY_TUNING = 0;
	public const COMPATIBILITY_STOCK = 1;

	public const PLACE_BOTH = 1;
	public const PLACE_FRONT = 2;
	public const PLACE_REAR = 3;

	public static function getCompatibilityOptions(): array
	{
		return [
			static::COMPATIBILITY_STOCK => 'Заводская комплектация',
			static::COMPATIBILITY_TUNING => 'Тюнинг',
		];
	}

	/**
	 * @return string
	 */
	public function getCompatibilityText(): string
	{
		$options = static::getCompatibilityOptions();
		return $options[(int)$this->compatibility] ?? 'Неизвестный тип';
	}

	public static function getPlaceOptions(): array
	{
		return [
			static::PLACE_BOTH => 'Обе оси',
			static::PLACE_FRONT => 'Передняя ось',
			static::PLACE_REAR => 'Задняя ось',
		];
	}

	public function getPlaceText(): string
	{
		$options = static::getPlaceOptions();
		return $options[(int)$this->place] ?? 'Неизвестный тип оси';
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%auto_tyres}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['model_id', 'sz', 'compatibility', 'width', 'pr', 'rad'], 'required'],
			[['compatibility', 'place'], 'string'],
			[['model_id', 'automodel_code_1c'], 'string', 'max' => 50],
			[['sz'], 'string', 'max' => 15],
			[['width'], 'string', 'max' => 4],
			[['pr'], 'string', 'max' => 5],
			[['rad'], 'string', 'max' => 6],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'model_id' => 'Model ID',
			'sz' => 'Sz',
			'compatibility' => 'Compatibility',
			'place' => 'Place',
			'width' => 'Width',
			'pr' => 'Pr',
			'rad' => 'Rad',
			'automodel_code_1c' => 'Automodel Code 1c',
		];
	}

	/**
	 * @inheritdoc
	 * @return AutoTyreQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new AutoTyreQuery(static::class);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getAutoModification()
	{
		return $this->hasOne(AutoModification::class, ['automodel_code_1c' => 'automodel_code_1c']);
	}

	public function getRadius(): float
	{
		return (float)str_replace('R', '', $this->rad);
	}

	public function getSizeParams(): array
	{
		return [
			'width' => (float)$this->width,
			'profile' => (float)$this->pr,
			'radius' => $this->getRadius(),
		];
	}

	public function fields()
	{
		$fields = [
			'compatibility' => static function (self $model) {
				return (int)$model->compatibility;
			},
			'compatibilityText',
			'place' => static function (self $model) {
				return (int)$model->place;
			},
			'placeText',
			'size' => 'sizeParams',
		];
		return $fields;
	}
}
