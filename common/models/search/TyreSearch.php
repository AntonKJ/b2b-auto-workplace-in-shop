<?php

namespace common\models\search;

use common\components\data\TyreGoodDataProvider;
use common\components\SearchInterface;
use common\components\sizes\SizeTyre;
use common\interfaces\OrderTypeGroupableInterface;
use common\models\AutoModification;
use common\models\CatalogIndex;
use common\models\OptUser;
use common\models\OptUserBrandHide;
use common\models\Region;
use common\models\Shop;
use common\models\ShopStock;
use common\models\TyreGood;
use domain\entities\PriceRange;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\sphinx\ActiveDataProvider;

class TyreSearch extends TyreGood implements SearchInterface
{

	public $q;

	public $brand;
	public $model;

	public $season;
	public $shop;

	public $runflat;
	public $pins;

	protected $_sizes;

	public $price;

	public $sr;
	public $li;

	public $auto;

	public $list;

	public $sale;

	protected $_searchParams;

	/**
	 * TyreSearch constructor.
	 * @param SearchParams|null $params
	 * @param array $config
	 */
	public function __construct(?SearchParams $params = null, array $config = [])
	{
		parent::__construct($config);
		$this->_searchParams = $params;
	}

	/**
	 * @return SearchParams|null
	 */
	public function getSearchParams(): ?SearchParams
	{
		return $this->_searchParams;
	}

	/**
	 * @param SearchParams|null $searchParams
	 * @return TyreSearch
	 */
	public function setSearchParams(?SearchParams $searchParams)
	{
		$this->_searchParams = $searchParams;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['q'], 'safe'],

			[['brand'], 'safe'],
			[['model'], 'safe'],

			[['season'], 'safe'],
			[['shop'], 'safe'],

			[['sale'], 'safe'],

			[['runflat'], 'safe'],
			[['pins'], 'safe'],

			[['sizes'], 'safe'],

			[['price'], 'safe'],

			[['sr'], 'safe'],
			[['li'], 'safe'],

			[['auto'], 'safe'],

			[['list'], 'safe'],
		];
	}

	public function setSizes($value)
	{

		if (is_array($value))
			$value = array_filter($value, function ($v) {
				return $v instanceof SizeTyre;
			});

		$this->_sizes = $value;
	}

	public function getSizes()
	{
		return $this->_sizes;
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

	protected function prepareWords($query)
	{

		if (!is_array($query)) {

			$query = trim(preg_replace('/[\s\.,]+/ui', ' ', $query));
			$query = preg_split('/[\s]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);

			$query = array_filter($query, function ($v) {
				return mb_strlen($v) >= 2;
			});
		}

		return $query;
	}

	/**
	 * @param SizeTyre $size
	 * @param null $goodPrefix
	 * @return Query
	 */
	protected function prepareSizeCondition(SizeTyre $size, $goodPrefix = null)
	{

		if (!empty($goodPrefix))
			$goodPrefix .= '.';

		$sizeQuery = new Query();

		$sizeQuery->andFilterWhere([
			"{$goodPrefix}width" => $size->width,
			"{$goodPrefix}pr" => $size->profile,
		]);

		if (!empty($size->radius))
			$sizeQuery->andWhere(['like', "{$goodPrefix}[[rad]]", 'R' . $size->radius . ($size->commerce ? 'C' : '') . '%', false]);

		return $sizeQuery;
	}

	/**
	 * @param array $data
	 * @param null $goodPrefix
	 * @return Query
	 */
	protected function prepareListCondition(array $data, $goodPrefix = null)
	{

		$fieldMapper = [
			'sku' => ['code_1c', 'manuf_code'],
		];

		if (!empty($goodPrefix))
			$goodPrefix .= '.';

		$queryList = new Query();
		foreach ($data as $listRow) {

			$queryListRow = new Query();
			foreach ($listRow as $field => $fieldValues) {

				if (!isset($fieldMapper[$field]))
					continue;

				foreach ($fieldMapper[$field] as $fieldName)
					$queryListRow->orWhere(["{$goodPrefix}[[{$fieldName}]]" => $fieldValues]);
			}

			if (is_array($queryListRow->where) && $queryListRow->where !== [])
				$queryList->orWhere($queryListRow->where);
		}

		return $queryList;
	}

	/**
	 * @param $params
	 * @return \common\models\query\TyreGoodQuery
	 */
	public function getSearchQuery($params = [])
	{

		$user = \Yii::$app->user;

		/**
		 * @var Region $region
		 */

		//регион берём из пользователя!!!
		$region = \Yii::$app->region->current;

		/**
		 * Ассоциированная группа типа заказов с текущим пользователем
		 * @var OrderTypeGroupableInterface $orderTypeGroup
		 */
		$orderTypeGroup = $region;

		$query = TyreGood::find();

		$query->alias('g');

		if (isset($params['sizes']) && is_array($params['sizes']) && [] !== $params['sizes']) {

			if (count($params['sizes']) == 1) {

				foreach ($params['sizes'] as $size) {

					$sQuery = $this->prepareSizeCondition($size, 'g');
					$query
						->andWhere($sQuery->where);
				}
			} else {

				// создаем новый подзапрос
				$subQuery = TyreGood::find()
					->alias('g')
					->select('g.p_t');

				// Генерируем запрос для спарки

				//todo нужно оптимизировать генерацию выборки
				$sizesQuery = new Query();

				/**
				 * @var QueryBuilder
				 */
				$queryBuilder = $this->db->queryBuilder;

				foreach (array_values($params['sizes']) as $sizeKey => $size) {

					// подготавливаем условие для размеров
					$sQuery = $this->prepareSizeCondition($size, 'g');

					$selectBuild = $queryBuilder->buildCondition($sQuery->where, $query->params);

					// добавляем логику для подсчётак ол-во товаров с данным размером в выборке
					$subQuery->addSelect(new Expression("SUM(IF({$selectBuild}, 1, 0)) score_{$sizeKey}"));

					// добавляем условие для фильтрации товаров по размерам
					$subQuery->andHaving("score_{$sizeKey} > 0");

					// добавляем условие
					$sizesQuery->orWhere($sQuery->where);
				}

				// добавляем условие на выборку
				$subQuery->andWhere($sizesQuery->where);

				// фильтр по списку артикулов и всему такому
				if (isset($params['list']) && is_array($params['list']) && [] !== $params['list']) {

					$listWhere = $this->prepareListCondition($params['list'], 'g')->where;

					if (is_array($listWhere) && [] !== $listWhere)
						$subQuery->andWhere($listWhere);
				}

				// фильтр наличия
				$subQuery->withZonePriceFilter($region);
				$subQuery->withOrderTypeStockFilter($orderTypeGroup);
				$subQuery->availableOnly();

				// Фильтр по sale
				if (isset($params['sale']) && (int)$params['sale'] > 0)
					$subQuery->bySales();

				// Фильтр по прайсу
				if (isset($params['price']))
					$subQuery->byPriceRange(new PriceRange($params['price']['from'] ?? null, $params['price']['to'] ?? null), 'zpFilter');

				if (isset($params['season']) && !empty($params['season']))
					$subQuery->bySeason($params['season']);

				if (isset($params['sr']) && !empty($params['sr']))
					$subQuery->bySpeedRating($params['sr']);

				if (isset($params['li']) && !empty($params['li']))
					$subQuery->byLoadIndex($params['li']);

				// Фильтр по flatrun
				if (isset($params['runflat']) && (int)$params['runflat'] > 0)
					$subQuery->byRunflat();

				// Фильтр по шипам
				if (isset($params['pins']) && (int)$params['pins'] > 0)
					$subQuery->byPins();

				// группируем по модели
				$subQuery->groupBy('g.p_t');

				// вставляем скопированный запрос, как подзапрос
				$query->innerJoin(['sm' => $subQuery], 'g.p_t = sm.p_t');

				// ну и добавляем условие к текущей выборке ??? может это лишнее
				$query->andWhere($sizesQuery->where);
			}
		}

		// Если есть ключевые слова
		if (isset($params['q']) && !empty($params['q'])) {

			// подготавливаем массив
			$prepareWords = $this->prepareWords($params['q']);

			// если массив не пустой
			if ([] !== $prepareWords) {

				// поключаем таблицу с индексами ключевых слов
				$query->innerJoin(['ci' => CatalogIndex::tableName()], [
					'ci.entity_id' => new Expression('`g`.`idx`'),
					'ci.entity_type' => static::getGoodEntityType(),
				]);

				// добавляем условие выборки по ключевым словам
				$query->andFilterWhere(['like', 'ci.words', $prepareWords]);
			}
		}

		// фильтр по списку артикулов и всему такому
		if (isset($params['list']) && is_array($params['list']) && [] !== $params['list']) {

			$listWhere = $this->prepareListCondition($params['list'], 'g')->where;

			if (is_array($listWhere) && [] !== $listWhere)
				$query->andWhere($listWhere);
		}

		// Фильтр по sale
		if (isset($params['sale']) && (int)$params['sale'] > 0)
			$query->bySales();

		// Фильтр по flatrun
		if (isset($params['runflat']) && (int)$params['runflat'] > 0)
			$query->byRunflat();

		// Фильтр по прайсу
		if (isset($params['price']))
			$query->byPriceRange(new PriceRange($params['price']['from'] ?? null, $params['price']['to'] ?? null), 'zpFilter');

		// Фильтр по шипам
		if (isset($params['pins']) && (int)$params['pins'] > 0)
			$query->byPins();

		if (isset($params['season']) && !empty($params['season']))
			$query->bySeason($params['season']);

		if (isset($params['sr']) && !empty($params['sr']))
			$query->bySpeedRating($params['sr']);

		if (isset($params['li']) && !empty($params['li']))
			$query->byLoadIndex($params['li']);

		if (isset($params['brand']) && !empty($params['brand']))
			$query->byBrandUrl($params['brand'], 'bFilter');

		if (isset($params['model']) && !empty($params['model']))
			$query->byModelUrl($params['model'], 'mFilter');

		// подключаем таблицу брендов
		$query->withBrandFilter();

		// подключаем таблицу моделей
		$query->withModelFilter();

		// фильтр наличия
		$query->withZonePriceFilter($region);
		$query->withOrderTypeStockFilter($orderTypeGroup);
		$query->availableOnly();

		// Только в наличии в магазинах
		if (isset($params['shop']) && !empty($params['shop'])) {

			$shopQuery = ShopStock::find()
				->alias('ss')
				->select(['ss.item_idx idx', 'SUM(ss.amount) qty'])
				->innerJoinWith(['shop' => function (ActiveQuery $q) use ($region) {
					$q->alias('s')
						->andOnCondition('s.region_id=:regionId AND s.not_show!=:notShow AND s.shop_id>0 AND s.is_active=:isActive', [
							':regionId' => $region->regionIdForShops,
							':notShow' => Shop::NOT_SHOW,
							':isActive' => Shop::IS_ACTIVE,
						]);
				}], false)
				->groupBy(['ss.item_idx'])
				->having('qty>0')
				->andWhere(['s.url' => $params['shop']]);

			$query->innerJoin(['sav' => $shopQuery], 'g.idx=sav.idx');

		}

		// Дополнительный фильтр по заблокированым брендам для пользователя
		if (!$user->isGuest) {

			$query->leftJoin(['ubh' => OptUserBrandHide::tableName()], 'ubh.entity_type=:hideBrandType AND ubh.user_id=:hideBrandUserId AND ubh.entity_code=g.prod_code', [
				':hideBrandUserId' => $user->id,
				':hideBrandType' => static::getGoodEntityType(),
			]);

			$query->andWhere('ubh.id IS NULL');
		}

		if (isset($params['auto']) && !empty($params['auto'])) {

			$query->innerJoin([
				'ar' => AutoModification::find()
					->alias('ar')
					->distinct(true)
					->select('atr.sz')
					->innerJoinWith(['tyreRel' => function (ActiveQuery $q) {
						$q
							->alias('atr');
					}], false)
					->andWhere(['ar.modification_slug' => $params['auto']]),
			], 'g.sz = ar.sz');
		}

		return $query;
	}

	public function getSearchAttributes()
	{
		return $this->getAttributes($this->safeAttributes());
	}

	/**
	 * @param $params
	 * @return TyreGoodDataProvider
	 */
	public function search($params)
	{

		$this->load($params) && $this->validate();

		$params = $this->getSearchAttributes();

		// формируем запрос к базе
		$query = $this->getSearchQuery($params);

		// ленивая загрузка
		$query->with(['brand', 'model']);

		$region = \Yii::$app->region->current;

		// это ленивая загрузка для прайсов и наличия
		$query
			->withPricesByRegion($region)
			->withStockByOrderTypeGroup($region);

		// и все это в датапровайдер
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => [
					'default' => SORT_ASC,
				],
				'attributes' => [
					'default' => [
						'asc' => [
							'bFilter.Position' => SORT_ASC,
							'mFilter.type' => SORT_ASC,
							'mFilter.position' => SORT_ASC,
							'g.p_t' => SORT_ASC,
							'g.rad' => SORT_ASC,
							'zpFilter.price' => SORT_ASC,
						],
						'desc' => [
							'bFilter.Position' => SORT_DESC,
							'mFilter.type' => SORT_DESC,
							'mFilter.position' => SORT_DESC,
							'g.p_t' => SORT_DESC,
							'g.rad' => SORT_DESC,
							'zpFilter.price' => SORT_DESC,
						],
						'default' => SORT_ASC,
					],
					'price' => [
						'asc' => ['zpFilter.price' => SORT_ASC],
						'desc' => ['zpFilter.price' => SORT_DESC],
						'default' => SORT_ASC,
					],
				],
			],
		]);

		return $dataProvider;
	}

	protected function isEmpty($value)
	{
		return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
	}
}
