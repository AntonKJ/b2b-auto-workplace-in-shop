<?php

namespace common\models\search;

use common\components\sizes\SizeTyre;
use common\models\TyreGood;
use yii\base\Model;
use yii\helpers\StringHelper;

class TyreSearchParams extends Model
{

	const VALUE_DELIMITER = ',';

	const TOKEN_SEASON_WINTER = 'зима';
	const TOKEN_SEASON_SUMMER = 'лето';

	const TOKEN_TYPE_TYRE = 'шины';
	const TOKEN_TYPE_RIMS = 'диски';

	const TOKEN_PINS = 'шипы';
	const TOKEN_RUNFLAT = 'runflat';

	// Ключевые слова
	public $q;

	// Сезон
	public $season;

	// Бренд
	public $brand;

	// Модель
	public $model;

	// Магазин
	public $shop;

	// Технология runflat
	public $runflat;

	// Шипы
	public $pins;

	// Размеры
	public $sizes;

	// Стоимость
	public $price;

	// Рейтинг скорости
	public $sr;

	// Индекс нагрузки
	public $li;

	// По автомобилю
	public $auto;

	protected $_filter;

	/**
	 * Опции для параметра сезон
	 * @return array
	 */
	static public function getSeasonMapperOptions()
	{
		return [
			static::TOKEN_SEASON_WINTER => mb_strtolower(TyreGood::SEASON_WINTER),
			static::TOKEN_SEASON_SUMMER => mb_strtolower(TyreGood::SEASON_SUMMER),
		];
	}

	/**
	 * Опции для параметра runflat
	 * @return array
	 */
	static public function getRunflatTokenOptions()
	{
		return [
			static::TOKEN_RUNFLAT => true,
		];
	}

	/**
	 * Опции для параметра шипы
	 * @return array
	 */
	static public function getPinsTokenOptions()
	{
		return [
			static::TOKEN_PINS => true,
		];
	}

	/**
	 * Меппер для нормализации поисковой строки
	 * @return array
	 */
	static public function getWordsNormalizeOptions()
	{
		return [

			'/\b(зим(?:а|нии)|winter|pbvf)\b/ui' => static::TOKEN_SEASON_WINTER,
			'/\b(лет(?:о|ние)|summer|ktnj)\b/ui' => static::TOKEN_SEASON_SUMMER,

			'/\b(ru?n[\s\-\/]?fla?t|fla?t[\s\-\/]?ru?n|fla?t|rft|ssr|(?:ран[\s\-]?)?фл[еэ]т(?:[\s\-]?ран)?)\b/ui' => static::TOKEN_RUNFLAT,

			'/\b(шипы?|pins?|ibgs?)\b/ui' => static::TOKEN_PINS,

			'/\b(шин[ыа]?)\b/ui' => static::TOKEN_TYPE_TYRE,
			'/\b(диск[и]?)\b/ui' => static::TOKEN_TYPE_RIMS,

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

			[['brand', 'model', 'shop'], 'filter', 'filter' => [$this, 'prepareFilterArrayParam'], 'skipOnArray' => false],

			[['season'], 'filter', 'filter' => [$this, 'prepareFilterSeasonParam'], 'skipOnArray' => false],

			[['runflat'], 'filter', 'filter' => [$this, 'prepareFilterRunflatParam'], 'skipOnArray' => false],

			[['pins'], 'filter', 'filter' => [$this, 'prepareFilterPinsParam'], 'skipOnArray' => false],

			[['sizes'], 'filter', 'filter' => [$this, 'prepareFilterSizesParam'], 'skipOnArray' => false],

			[['price'], 'filter', 'filter' => [$this, 'prepareFilterPriceParam'], 'skipOnArray' => false],

			[['sr'], 'filter', 'filter' => [$this, 'prepareFilterSpeedRatingParam'], 'skipOnArray' => false],

			[['li'], 'filter', 'filter' => [$this, 'prepareFilterLoadIndexParam'], 'skipOnArray' => false],
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
	 * Подготавливаем параметры фильтра по сезону
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterSeasonParam($value)
	{

		$options = static::getSeasonMapperOptions();

		$value = $this->prepareFilterArrayParam($value);

		if ([] !== $value) {

			$value = array_filter($value, function ($v) use ($options) {
				return in_array(mb_strtolower($v), $options);
			});
		}

		if (!empty($this->q))
			foreach ($options as $token => $entityType) {

				$pattern = '/\b' . preg_quote($token) . '\b/ui';
				if (preg_match($pattern, $this->q)) {

					$this->q = trim(preg_replace($pattern, '', $this->q));
					$value[] = $entityType;
				}
			}

		if ($value !== [])
			$value = array_unique($value);

		return $value;
	}

	/**
	 * Определяем runflat
	 * @param $value
	 * @return bool|mixed|null
	 */
	public function prepareFilterRunflatParam($value)
	{

		if(!is_string($value))
			return null;

		$value = mb_strlen($value) > 0 && (int)$value > 0;

		$options = static::getRunflatTokenOptions();

		if (!empty($this->q))
			foreach ($options as $token => $entityType) {

				$pattern = '/\b' . preg_quote($token) . '\b/ui';
				if (preg_match($pattern, $this->q)) {

					$this->q = trim(preg_replace($pattern, '', $this->q));
					$value = $entityType;
				}
			}

		if ($value === false)
			$value = null;

		return $value;
	}

	/**
	 * Определяем параметр фильтра по шипам
	 * @param $value
	 * @return bool|mixed|null
	 */
	public function prepareFilterPinsParam($value)
	{

		if(!is_string($value))
			return null;

		$value = mb_strlen($value) > 0 && (int)$value > 0;

		$options = static::getPinsTokenOptions();

		if (!empty($this->q))
			foreach ($options as $token => $entityType) {

				$pattern = '/\b' . preg_quote($token) . '\b/ui';
				if (preg_match($pattern, $this->q)) {

					$this->q = trim(preg_replace($pattern, '', $this->q));
					$value = $entityType;
				}
			}

		if ($value === false)
			$value = null;

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
				 * @var SizeTyre $size
				 */

				$sizes = SizeTyre::createFromString($sizeString);
				if ([] !== $sizes)
					foreach ($sizes as $size)
						$value[$size->format()] = $size;
			}

			if (is_array($sizeString)) {

				$params = [];

				if (isset($sizeString['width']) && (float)$sizeString['width'] > 0)
					$params['width'] = (float)$sizeString['width'];

				if (isset($sizeString['profile']) && (float)$sizeString['profile'] > 0)
					$params['profile'] = (float)$sizeString['profile'];

				if (isset($sizeString['radius']) && (float)$sizeString['radius'] > 0)
					$params['radius'] = (float)$sizeString['radius'];

				if ($params !== []) {

					$size = new SizeTyre($params);
					$value[$size->format()] = $size;

				}

			}
		}

		if (!empty($this->q)) {

			$sizes = SizeTyre::createFromString($this->q);
			if ([] !== $sizes)
				foreach ($sizes as $size) {

					$this->q = str_replace(implode(' ', $size->parts), '', $this->q);
					$value[$size->format()] = $size;
				}

			$this->q = trim($this->q);

			if ($value !== [])
				$value = array_values($value);
		}

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

		if (is_string($value) && mb_strlen($this->price) > 0) {

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
	 * Подготавливаем параметры фильтра по рейтингу скорости
	 * @param $value
	 * @return array
	 */
	public function prepareFilterSpeedRatingParam($value)
	{

		$value = $this->prepareFilterArrayParam($value);

		if ([] !== $value) {

			$options = TyreGood::getSpeedRatingOptions();

			$value = array_filter($value, function ($v) use ($options) {
				return isset($options[mb_strtoupper($v)]);
			});

			if ($value !== [])
				$value = array_unique($value);

		}

		return $value;
	}

	/**
	 * Подготавливаем параметры фильтра по индексу нагрузки
	 * @param $value
	 * @return array
	 */
	public function prepareFilterLoadIndexParam($value)
	{

		$value = $this->prepareFilterArrayParam($value);

		if ([] !== $value) {

			$value = array_map(function ($v) {
				return str_replace('_', '/', $v);
			}, $value);

			$options = TyreGood::getLoadIndexOptions();

			$value = array_filter($value, function ($v) use ($options) {

				if (strpos($v, '/') !== false) {

					$valid = true;
					foreach (explode('/', $v) as $pv) {
						$valid = $valid && (!empty($pv) && isset($options[$pv]));
					}

					return $valid;
				}

				$v = mb_strtoupper(trim($v));
				return !empty($v) && isset($options[$v]);
			});

			if ($value !== [])
				$value = array_unique($value);

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
