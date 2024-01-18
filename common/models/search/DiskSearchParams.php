<?php

namespace common\models\search;

use common\components\sizes\SizeRim;
use yii\base\Model;
use yii\helpers\StringHelper;

class DiskSearchParams extends Model
{

	const VALUE_DELIMITER = ',';

	// Ключевые слова
	public $q;

	// Бренд
	public $brand;

	// Модель
	public $model;

	// Магазин
	public $shop;

	// Размеры
	public $sizes;

	// Стоимость
	public $price;

	// Вариация
	public $variation;

	// Тип материала
	public $material;

	// Цвет
	public $color;

	// Автомобиль
	public $auto;

	protected $_filter;

	/**
	 * Меппер для нормализации поисковой строки
	 * @return array
	 */
	static public function getWordsNormalizeOptions()
	{
		return [

		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

			[['q'], 'filter', 'filter' => [$this, 'prepareFilterQueryParam']],
			[['q'], 'string', 'max' => 255],

			[['auto'], 'filter', 'filter' => [$this, 'prepareFilterStringParam']],
			[['auto'], 'string', 'max' => 255],

			[['brand', 'model', 'shop', 'variation', 'color', 'material'], 'filter', 'filter' => [$this, 'prepareFilterArrayParam'], 'skipOnArray' => false],

			[['sizes'], 'filter', 'filter' => [$this, 'prepareFilterSizesParam'], 'skipOnArray' => false],

			[['price'], 'filter', 'filter' => [$this, 'prepareFilterPriceParam'], 'skipOnArray' => false],

		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		return Model::scenarios();
	}

	public function formName()
	{
		return '';
	}

	/**
	 * Возвращает массив активных параметров фильтра
	 * @param null $names
	 * @param array $except
	 * @return array
	 */
	public function getFilterParams($names = null, $except = [])
	{
		$values = $this->getAttributes($names, $except);

		$values = array_filter($values, function ($v) {
			return ($isA = is_array($v)) && [] !== $v || !$isA && !empty($v);
		});

		return $values;
	}

	/**
	 * Подготавливаем параметр фильтра по ключевым словам
	 * @param $value
	 * @return mixed|string
	 */
	public function prepareFilterQueryParam($value)
	{

		$value = $this->prepareFilterStringParam($value);

		$wordsPatterns = static::getWordsNormalizeOptions();
		$value = preg_replace(array_keys($wordsPatterns), array_values($wordsPatterns), $value);

		return $value;
	}

	/**
	 * Подготовка строковых параметров фильтра
	 * @param $value
	 * @return string
	 */
	public function prepareFilterStringParam($value)
	{
		return trim(preg_replace('/\s{2,}/ui', ' ', $value));
	}

	/**
	 * Подготовка стандартного фильтра по множественным параметрам
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterArrayParam($value)
	{

		if (is_string($value))
			$value = $this->prepareFilterStringParam($value);

		if (!is_array($value))
			$value = StringHelper::explode($value, static::VALUE_DELIMITER, true, true);

		// Отфильтровываем пустые массивы и строки
		$value = array_filter($value, function ($v) {
			return ($isA = is_array($v)) && [] !== $v || !$isA && mb_strlen($v) > 0;
		});

		if ($value !== [])
			$value = array_unique($value);

		return $value;
	}

	/**
	 * Подготавливаем параметры фильтра по размерам
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterSizesParam($value)
	{

		if (is_string($value))
			$value = $this->prepareFilterStringParam($value);

		// Если входной параметр строка
		if (!is_array($value))
			$value = StringHelper::explode($value, static::VALUE_DELIMITER, true, true);

		$filterSizeParams = function ($v) use (&$filterSizeParams) {

			if (is_string($v))
				$v = mb_strtolower(trim($v));

			if (is_array($v))
				$v = array_filter($v, $filterSizeParams);

			return is_string($v) && !empty($v) || is_array($v) && [] !== $v;
		};

		$valuePrepared = array_filter($value, $filterSizeParams);

		$value = [];
		foreach ($valuePrepared as $sizeString) {

			if (is_string($sizeString)) {

				/**
				 * @var SizeRim $size
				 */

				$sizes = SizeRim::createFromString($sizeString);
				if ([] !== $sizes)
					foreach ($sizes as $size)
						$value[$size->format()] = $size;
			}

			if (is_array($sizeString)) {

				$params = [];

				if (isset($sizeString['diameter']) && (float)$sizeString['diameter'] > 0)
					$params['diameter'] = (float)$sizeString['diameter'];

				if (isset($sizeString['width']) && (float)$sizeString['width'] > 0)
					$params['width'] = (float)$sizeString['width'];

				if (isset($sizeString['pn']) && (float)$sizeString['pn'] > 0)
					$params['pn'] = (float)$sizeString['pn'];

				if (isset($sizeString['pcd']) && (float)$sizeString['pcd'] > 0)
					$params['pcd'] = (float)$sizeString['pcd'];

				if (isset($sizeString['et']) && (float)$sizeString['et'] > 0)
					$params['et'] = (float)$sizeString['et'];

				if (isset($sizeString['cb']) && (float)$sizeString['cb'] > 0)
					$params['cb'] = (float)$sizeString['cb'];

				if ($params !== []) {

					$size = new SizeRim($params);

					if ([] !== $size->getFilledSizeParts())
						$value[$size->format()] = $size;
				}

			}
		}

		if (!empty($this->q)) {

			$size = SizeRim::createFromString($this->q);
			if ([] !== $size->getFilledSizeParts()) {

				$this->q = str_replace(implode(' ', $size->parts), '', $this->q);
				$value[$size->format()] = $size;

				$this->q = trim($this->q);
			}

		}

		if ($value !== [])
			$value = array_values($value);

		return $value;
	}

	/**
	 * Подготавливаем параметры фильтра по прайсу
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterPriceParam($value)
	{

		if (is_string($value))
			$value = $this->prepareFilterStringParam($value);

		if (is_string($value) && mb_strlen($value) > 0) {

			$prices = StringHelper::explode($value, static::VALUE_DELIMITER, true);
			$prices = array_splice($prices, 0, 2);

			$prices = array_map(function ($v) {
				return (float)$v;
			}, $prices);

			$value = [];
			if (count($prices) < 2) {

				$value['to'] = $prices[0];
			} else {

				if ($prices[0] > 0 && $prices[1] > 0)
					sort($prices);

				if ($prices[0])
					$value['from'] = $prices[0];

				if ($prices[1])
					$value['to'] = $prices[1];
			}
		} elseif (is_array($value) && [] !== $value) {

			$filter = [];

			if (isset($value['from']) && (float)$value['from'] > 0)
				$filter['from'] = (float)$value['from'];

			if (isset($value['to']) && (float)$value['to'] > 0)
				$filter['to'] = (float)$value['to'];

			if (count($filter) > 1 && $filter['from'] > $filter['to'])
				list($filter['from'], $filter['to']) = [$filter['to'], $filter['from']];

			$value = [] === $filter ? null : $filter;
		}

		return $value;
	}

	/**
	 * @param Model $model
	 */
	public function loadAttributes(Model $model)
	{
		$model->setAttributes($this->getFilterParams());
	}

}
