<?php

namespace common\models\search;

use common\components\sizes\SizeTyre;
use common\models\TyreGood;
use domain\entities\SizeDisk;
use domain\SizeDiskBuilder;
use domain\SizeTyreBuilder;
use yii\base\Model;
use yii\helpers\StringHelper;

class SearchParams extends Model
{

	const SEARCH_TYPE_TYRE = 'tyre';
	const SEARCH_TYPE_DISK = 'disk';

	const VALUE_DELIMITER = ',';

	const TOKEN_SEASON_WINTER = 'зима';
	const TOKEN_SEASON_SUMMER = 'лето';

	const TOKEN_TYPE_TYRE = 'шины';
	const TOKEN_TYPE_DISK = 'диски';

	const TOKEN_PINS = 'шипы';
	const TOKEN_RUNFLAT = 'runflat';

	const SCENARIO_BYLIST = 'by_list';

	// ----------------- общие

	/**
	 * @var
	 */
	public $sku; // артикул

	public $type; // Тип поиска

	// Распродажа
	public $sale;

	// По списку
	public $list;

	// Ключевые слова
	public $q;

	// Бренд
	public $brand;

	// Модель
	public $model;

	// Магазин
	public $shop;

	// Стоимость
	public $price;

	// Размеры
	public $sizes;

	// По автомобилю
	public $auto;

	// Год модификации автомобиля
	public $year;

	// Идентификатор адреса HASH
	public $addressId;

	// ----------------- шины

	// Сезон
	public $season;

	// Технология runflat
	public $runflat;

	// Шипы
	public $pins;

	// Рейтинг скорости
	public $sr;

	// Индекс нагрузки
	public $li;

	// ----------------- диски

	// Вариация
	public $variation;

	// Тип материала
	public $material;

	// Цвет
	public $color;


	protected $_filter;

	protected $_input;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

			// общие

			[['q'], 'filter', 'filter' => [$this, 'prepareFilterQueryParam']],
			[['q'], 'string', 'max' => 255],

			[['type'], 'filter', 'filter' => [$this, 'prepareFilterTypeParam'], 'skipOnArray' => false],

			[['auto'], 'filter', 'filter' => [$this, 'prepareFilterStringParam']],
			[['auto'], 'string', 'max' => 255],

			[['price'], 'filter', 'filter' => [$this, 'prepareFilterPriceParam'], 'skipOnArray' => false],

			[['sku'], 'filter', 'filter' => [$this, 'prepareFilterArrayParam'], 'skipOnArray' => false],

			[['brand', 'model', 'shop'], 'filter', 'filter' => [$this, 'prepareFilterArrayParam'], 'skipOnArray' => false],

			[['addressId'], 'filter', 'filter' => [$this, 'prepareFilterArrayParam'], 'skipOnArray' => false],

			// диски
			[['variation', 'color', 'material'], 'filter', 'filter' => [$this, 'prepareFilterArrayParam'], 'skipOnArray' => false],

			// шины
			[['runflat'], 'filter', 'filter' => [$this, 'prepareFilterRunflatParam'], 'skipOnArray' => false],
			[['pins'], 'filter', 'filter' => [$this, 'prepareFilterPinsParam'], 'skipOnArray' => false],
			[['season'], 'filter', 'filter' => [$this, 'prepareFilterSeasonParam'], 'skipOnArray' => false],
			[['sr'], 'filter', 'filter' => [$this, 'prepareFilterSpeedRatingParam'], 'skipOnArray' => false],
			[['li'], 'filter', 'filter' => [$this, 'prepareFilterLoadIndexParam'], 'skipOnArray' => false],

			[['sale'], 'filter', 'filter' => [$this, 'prepareFilterSaleParam'], 'skipOnArray' => false],

			[['year'], 'filter', 'filter' => [$this, 'prepareFilterYearParam'], 'skipOnArray' => false],
			[['year'], 'integer', 'min' => 1900, 'max' => 2099],

			[['sizes'], 'filter', 'filter' => [$this, 'prepareFilterSizesParam'], 'skipOnArray' => false],

			[['list'], 'required', 'on' => [static::SCENARIO_BYLIST]],
			[['list'], 'filter', 'filter' => [$this, 'prepareFilterListParam'], 'skipOnArray' => false],

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

	public function attributeLabels()
	{
		$labels = parent::attributeLabels();

		$labels['list'] = 'Список артикулов';

		return $labels;
	}

	/**
	 * @param bool $refresh
	 * @return array|null
	 */
	public function getFilterParamsAsQueryString(bool $refresh = false, $type = null)
	{

		static $cache;

		if ($cache === null || $refresh) {

			$cache = [];

			$params = $this->getFilterParams();

			if (isset($params['type']) && is_array($params['type']) && [] !== $params['type']) {

				$typeOptions = static::getTypeTokenOptions();
				$params['type'] = array_map(function ($v) use ($typeOptions) {
					return $typeOptions[$v];
				}, $params['type']);
			}

			if (isset($params['sizes'])) {

				$sizes = [];
				foreach ($params['sizes'] as $size) {

					$sType = null;
					switch (true) {

						case $size instanceof \domain\entities\SizeTyre:

							$sType = static::SEARCH_TYPE_TYRE;
							break;

						case $size instanceof \domain\entities\SizeDisk:

							$sType = static::SEARCH_TYPE_DISK;
							break;
					}

					$sizes[$sType][] = $size->format();
				}

				$params['sizes'] = $sizes;
			}

			if (isset($params['pins']) && (bool)$params['pins'])
				$params['pins'] = static::TOKEN_PINS;

			if (isset($params['runflat']) && (bool)$params['runflat'])
				$params['runflat'] = static::TOKEN_RUNFLAT;

			if (isset($params['season'])) {

				$seasonOptions = array_flip(static::getSeasonMapperOptions());
				$params['season'] = array_map(function ($v) use ($seasonOptions) {
					return $seasonOptions[$v];
				}, $params['season']);
			}

			$cache = $params;
		}

		return $cache;
	}

	public function prepareFilterYearParam($value)
	{

		$value = trim($value);
		if ((int)$value == 0 && !$this->isEmpty($this->q)) {

			$year = $this->prepareYears($this->q);
			if ($year !== false && (int)$year > 0) {

				$value = $year;
				$this->q = trim(str_replace($year, '', $this->q));
			}
		}

		return $value;
	}

	protected function isEmpty($value)
	{
		return $value === '' || $value === [] || $value === null || (\is_string($value) && trim($value) === '');
	}

	protected function prepareYears($query)
	{

		preg_match_all('/ \b (?P<year> 19\d{2} | 20\d{2} ) \b /uix', $query, $match);
		return reset($match['year']);
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

			$isA = \is_array($v);
			return ($isA && [] !== $v) || (!$isA && !empty($v));
		});

		return $values;
	}

	/**
	 * Опции типов товаров
	 * @return array
	 */
	static public function getTypeTokenOptions()
	{
		return [
			static::SEARCH_TYPE_TYRE => static::TOKEN_TYPE_TYRE,
			static::SEARCH_TYPE_DISK => static::TOKEN_TYPE_DISK,
		];
	}

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

			'/\b(шин[ыа]?|iby[sf]?|t[yi]res?)\b/ui' => static::TOKEN_TYPE_TYRE,
			'/\b(диск[и]?|lbcr[b]?|disks?|rims?|whe+ls?)\b/ui' => static::TOKEN_TYPE_DISK,

		];
	}

	/**
	 * Подготавливаем параметры фильтра по сезону
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterSeasonParam($value)
	{

		$options = static::getSeasonMapperOptions();

		/** @var array $value */
		$value = $this->prepareFilterArrayParam($value);

		if ([] !== $value) {

			$value = array_filter($value, function ($v) use ($options) {
				return \in_array(mb_strtolower($v), $options);
			});
		}

		if (!empty($this->q))
			foreach ($options as $token => $entityType) {

				$pattern = '/\b' . preg_quote($token, '/') . '\b/ui';
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
	 * Подготовка стандартного фильтра по множественным параметрам
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterArrayParam($value)
	{

		if (\is_string($value))
			$value = $this->prepareFilterStringParam($value);

		if (!\is_array($value))
			$value = StringHelper::explode($value, static::VALUE_DELIMITER, true, true);

		// Отфильтровываем пустые массивы и строки
		$value = array_filter($value, function ($v) {

			$isA = \is_array($v);
			return ($isA && [] !== $v) || (!$isA && mb_strlen($v) > 0);
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
	public function prepareFilterTypeParam($value)
	{

		$options = static::getTypeTokenOptions();

		/** @var array $value */
		$value = $this->prepareFilterArrayParam($value);

		if ([] !== $value) {

			$value = array_filter($value, function ($v) use ($options) {
				return \in_array(mb_strtolower($v), array_values($options));
			});
		}

		if (!empty($this->q))
			foreach ($options as $token => $entityType) {

				$pattern = '/\b' . preg_quote($entityType, '/') . '\b/ui';
				if (preg_match($pattern, $this->q)) {

					//$this->q = trim(preg_replace($pattern, '', $this->q));
					$value[] = $token;
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

		if (!\is_string($value))
			return null;

		$value = $value !== '' && (int)$value > 0;

		$options = static::getRunflatTokenOptions();

		if (!empty($this->q))
			foreach ($options as $token => $entityType) {

				$pattern = '/\b' . preg_quote($token, '/') . '\b/ui';
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
	 * Определяем sale
	 * @param $value
	 * @return bool|mixed|null
	 */
	public function prepareFilterSaleParam($value)
	{

		if (!\is_string($value))
			return null;

		$value = $value !== '' && (int)$value > 0;

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

		$options = [0, 1];

		$value = $this->prepareFilterArrayParam($value);

		if ([] !== $value) {

			$value = array_filter($value, function ($v) use ($options) {
				return is_numeric($v) && \in_array((int)$v, array_values($options));
			});
		}

		if ($value !== [])
			$value = array_unique($value);

		return $value;

		/*		if (!\is_string($value))
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

				return $value;*/
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

	public function prepareFilterSizesParam($value)
	{

		$searchType = $this->searchGoodTypeClassification();

		$sizes = [];
		if ([] === $searchType || isset($searchType[static::SEARCH_TYPE_TYRE]))
			$sizes = array_merge($sizes, $this->prepareFilterTyreSizesParam($value));

		if ([] === $searchType || isset($searchType[static::SEARCH_TYPE_DISK]))
			$sizes = array_merge($sizes, $this->prepareFilterDiskSizesParam($value));

		$parts = [];
		foreach ($sizes as $size)
			if ([] !== $size->getParts())
				$parts[] = implode(' ', $size->getParts());

		// Если больше одного фрагмента размера, сортируем от длинных к коротким
		// и удаляем эти куски из поискового запроса
		if (\count($parts) > 1)
			usort($parts, function ($a, $b) {
				return mb_strlen($b) - mb_strlen($a);
			});

		if ([] !== $parts) {

			foreach ($parts as $part)
				$this->q = trim(preg_replace('/\s{2,}/ui', ' ', str_replace($parts, '', $this->q)));
		}

		return array_values($sizes);
	}

	protected function searchGoodTypeClassification()
	{

		$types = [];

		if (is_array($this->type) && [] !== $this->type)
			foreach ($this->type as $type)
				$types[$type] = isset($types[$type]) ? ($types[$type] + 1) : 1;

		foreach (['season', 'pins', 'runflat', 'sr', 'li'] as $param)
			if (!empty($this->{$param}))
				$types[static::SEARCH_TYPE_TYRE] = isset($types[static::SEARCH_TYPE_TYRE]) ? ($types[static::SEARCH_TYPE_TYRE] + 1) : 1;

		foreach (['variation', 'color', 'material'] as $param)
			if (!empty($this->{$param}))
				$types[static::SEARCH_TYPE_DISK] = isset($types[static::SEARCH_TYPE_DISK]) ? ($types[static::SEARCH_TYPE_DISK] + 1) : 1;

		return $types;
	}

	/**
	 * Подготавливаем параметры фильтра по размерам
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterTyreSizesParam($value)
	{

		if (\is_string($value))
			$value = $this->prepareFilterStringParam($value);

		// Если входной параметр строка
		if (!\is_array($value))
			$value = StringHelper::explode($value, static::VALUE_DELIMITER, true, true);

		$filterSizeParams = function ($v) use (&$filterSizeParams) {

			if (\is_string($v))
				$v = mb_strtolower(trim($v));

			if (\is_array($v))
				$v = array_filter($v, $filterSizeParams);

			return (\is_string($v) && !empty($v)) || (\is_array($v) && [] !== $v);
		};

		$valuePrepared = array_filter($value, $filterSizeParams);

		$value = [];
		foreach ($valuePrepared as $sizeString) {

			if (\is_string($sizeString)) {

				/**
				 * @var SizeTyre $size
				 */
				$sizes = SizeTyreBuilder::createFromString($sizeString);
				if ([] !== $sizes)
					foreach ($sizes as $size)
						$value[$size->format()] = $size;

			}

			if (\is_array($sizeString)) {

				$sizeBuilder = SizeTyreBuilder::instance();

				if (isset($sizeString['width']) && (float)$sizeString['width'] > 0)
					$sizeBuilder->withWidth((float)$sizeString['width']);

				if (isset($sizeString['profile']) && (float)$sizeString['profile'] > 0)
					$sizeBuilder->withProfile((float)$sizeString['profile']);

				if (isset($sizeString['radius']) && (float)$sizeString['radius'] > 0) {

					$sizeBuilder
						->withRadius((float)$sizeString['radius']);

					$sizeBuilder
						->withCommerce((bool)preg_match('/[cс]+$/ui', $sizeString['radius']));
				}

				if ($sizeBuilder->getFilledParams() !== []) {

					$size = $sizeBuilder->build();
					$value[$size->format()] = $size;
				}

			}
		}

		if (!empty($this->q)) {

			/**
			 * @var SizeTyre $size
			 */
			$sizes = SizeTyreBuilder::createFromString($this->q);
			if ([] !== $sizes)
				foreach ($sizes as $size)
					$value[$size->format()] = $size;
		}

		if ($value !== [])
			$value = array_values($value);

		return $value;
	}

	/**
	 * Подготавливаем параметры фильтра по размерам
	 * @param $value
	 * @return array|string
	 */
	public function prepareFilterDiskSizesParam($value)
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

			return (\is_string($v) && !empty($v)) || (\is_array($v) && [] !== $v);
		};

		$valuePrepared = array_filter($value, $filterSizeParams);

		$value = [];
		foreach ($valuePrepared as $sizeString) {

			if (\is_string($sizeString)) {

				/**
				 * @var SizeDisk $size
				 */
				$sizes = SizeDiskBuilder::createFromString($sizeString);
				if ([] !== $sizes)
					foreach ($sizes as $size)
						$value[$size->format()] = $size;
			}

			if (is_array($sizeString)) {

				$sizeBuilder = SizeDiskBuilder::instance();

				if (isset($sizeString['diameter']) && (float)$sizeString['diameter'] > 0)
					$sizeBuilder->withDiameter($sizeString['diameter']);

				if (isset($sizeString['width']) && (float)$sizeString['width'] > 0)
					$sizeBuilder->withWidth($sizeString['width']);

				if (isset($sizeString['pn']) && (float)$sizeString['pn'] > 0)
					$sizeBuilder->withPn((float)$sizeString['pn']);

				if (isset($sizeString['pcd']) && (float)$sizeString['pcd'] > 0)
					$sizeBuilder->withPcd($sizeString['pcd']);

				if (isset($sizeString['et']) && (float)$sizeString['et'] > 0)
					$sizeBuilder->withEt($sizeString['et']);

				if (isset($sizeString['cb']) && (float)$sizeString['cb'] > 0)
					$sizeBuilder->withCb($sizeString['cb']);

				if ($sizeBuilder->getFilledParams() !== []) {

					$size = $sizeBuilder->build();
					$value[$size->format()] = $size;
				}
			}
		}

		if (!empty($this->q)) {

			/**
			 * @var SizeDisk $size
			 */
			$sizes = SizeDiskBuilder::createFromString($this->q);
			if ([] !== $sizes)
				foreach ($sizes as $size)
					$value[$size->format()] = $size;
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

		if (\is_string($value))
			$value = $this->prepareFilterStringParam($value);

		if (\is_string($value) && mb_strlen($this->price) > 0) {

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
		} elseif (\is_array($value) && [] !== $value) {

			$filter = [];

			if (isset($value['from']) && (float)$value['from'] > 0)
				$filter['from'] = (float)$value['from'];

			if (isset($value['to']) && (float)$value['to'] > 0)
				$filter['to'] = (float)$value['to'];

			if (\count($filter) > 1 && $filter['from'] > $filter['to'])
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
	 * Подготавливаем параметры фильтра по excel списку
	 * @param $value
	 * @return array
	 */
	public function prepareFilterListParam($value)
	{

		$patterns = [
			'sku' => '/^((?:(?=.*\d)([-\da-zа-я]{4,}))|(\d{5,8}))$/ui',
		];

		// разбиваем текст на строки
		$value = StringHelper::explode(trim($value), "\n", true, true);

		// разбиваем строки на колонки
		$value = array_map(function ($v) use ($patterns) {

			$row = [];

			$columns = StringHelper::explode($v, "\t");
			foreach ($columns as $col)
				foreach ($patterns as $field => $pt)
					if (preg_match($pt, $col))
						$row[$field][] = $col;

			return $row;
		}, $value);

		$value = array_filter($value, function ($v) {
			return [] !== $v;
		});

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
