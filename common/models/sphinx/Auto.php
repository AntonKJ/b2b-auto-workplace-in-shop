<?php

namespace common\models\sphinx;

use yii\sphinx\ActiveRecord;

class Auto extends ActiveRecord
{

	/**
	 * @return string the name of the index associated with this ActiveRecord class.
	 */
	public static function indexName()
	{
		return 'myexample_auto';
	}

	/**
	 * @inheritdoc
	 * @return AutoQuery
	 */
	public static function find()
	{
		return new AutoQuery(get_called_class());
	}

	public function getTitle()
	{
		return trim("{$this->brand} {$this->model} {$this->rangeText}");
	}

	public function getYearStart()
	{
		return ($y = (int)$this->modification_start) == 0 || $y == 1000 || $y == 3000 ? null : $y;
	}

	public function getYearEnd()
	{
		return ($y = (int)$this->modification_end) == 0 || $y == 1000 || $y == 3000 ? null : $y;
	}

	public function getRange()
	{
		return [
			'start' => $this->yearStart,
			'end' => $this->yearEnd,
		];
	}

	public function getRangeText()
	{

		$range = array_map(function ($v) {
			return empty($v) ? '*' : $v;
		}, $this->range);

		return null === $range['start'] && null === $range['end'] ? null : '(' . trim(implode(' – ', $range)) . ')';
	}

	public function fields()
	{
		$fields = [
			'id' => 'modification_id',
			'slug' => 'modification_slug',
			'brandId' => 'brand_slug',
			'modelId' => 'model_slug',
			'title',
			'range',
			'rangeText',
		];

		return $fields;
	}

}