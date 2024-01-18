<?php

namespace common\models\search;

use common\components\data\TyreGoodDataProvider;
use common\components\SearchInterface;
use common\components\sizes\SizeRim;
use common\components\sizes\SizeTyre;
use common\models\CatalogIndex;
use common\models\DiskGood;
use common\models\OptUser;
use common\models\OptUserBrandHide;
use common\models\Region;
use common\models\Shop;
use common\models\ShopStock;
use common\models\ZonePrice;
use domain\entities\region\RegionEntityInterface;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DiskSearch extends DiskGood implements SearchInterface
{

	public $q;

	public $brand;
	public $model;

	public $variation;
	public $color;
	public $material;

	public $shop;

	protected $_sizes;

	public $price;

	public $auto;

	public $list;

	public $sale;

	protected $_searchParams;

	/**
	 * DiskSearch constructor.
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
	 * @return DiskSearch
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

			[['variation'], 'safe'],

			[['color'], 'safe'],

			[['material'], 'safe'],

			[['shop'], 'safe'],

			[['sizes'], 'safe'],

			[['price'], 'safe'],

			[['auto'], 'safe'],

			[['list'], 'safe'],

			[['sale'], 'safe'],

		];
	}

	public function setSizes($value)
	{

		if (is_array($value))
			$value = array_filter($value, function ($v) {
				return $v instanceof SizeRim;
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
	protected function prepareSizeCondition(SizeRim $size, $goodPrefix = null)
	{

		if (!empty($goodPrefix))
			$goodPrefix .= '.';

		$sizeQuery = (new Query());

		$sizeQuery->andFilterWhere([
			"{$goodPrefix}diameter" => $size->diameter,
			"{$goodPrefix}width" => $size->width,
			"{$goodPrefix}pn" => $size->pn,
			"{$goodPrefix}pcd" => $size->pcd,
			"{$goodPrefix}et" => $size->et,
			"{$goodPrefix}dia" => $size->cb,
		]);

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
			'sku' => ['disk_id', 'manuf_code'],
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

			if(is_array($queryListRow->where) && $queryListRow->where !== [])
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

		$query = DiskGood::find();

		$query->alias('g');

		// фильтр по списку артикулов и всему такому
		if (isset($params['list']) && is_array($params['list']) && [] !== $params['list']) {

			$listWhere = $this->prepareListCondition($params['list'], 'g')->where;

			if(is_array($listWhere) && [] !== $listWhere)
				$query->andWhere($listWhere);
		}

		if (isset($params['sizes']) && is_array($params['sizes']) && [] !== $params['sizes']) {

			foreach ($params['sizes'] as $size) {

				$sQuery = $this->prepareSizeCondition($size, 'g');
				$query
					->andWhere($sQuery->where);
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
					'ci.entity_id' => new Expression('`g`.`disk_id`'),
					'ci.entity_type' => static::getGoodEntityType(),
				]);

				// добавляем условие выборки по ключевым словам
				$query->andFilterWhere(['like', 'ci.words', $prepareWords]);
			}
		}

		// Фильтр по прайсу
		if (isset($params['price']) && !empty($params['price'])) {

			if (isset($params['price']['from']) && isset($params['price']['to']))
				$query->andWhere(['between', 'zpFilter.price', $params['price']['from'], $params['price']['to']]);
			elseif (isset($params['price']['from']))
				$query->andWhere(['>=', 'zpFilter.price', $params['price']['from']]);
			elseif (isset($params['price']['to']))
				$query->andWhere(['<=', 'zpFilter.price', $params['price']['to']]);
		}

		// grid filtering conditions
		$query->andFilterWhere([
			'and',
			[
				'bFilter.code' => ArrayHelper::getValue($params, 'brand', []),
				'mFilter.slug' => ArrayHelper::getValue($params, 'model', []),
				'mFilter.type_id' => ArrayHelper::getValue($params, 'material', []),
				'vFilter.color_id' => ArrayHelper::getValue($params, 'color', []),
				'g.variation_id' => ArrayHelper::getValue($params, 'variation', []),
			],
		]);

		// подключаем таблицу брендов
		$query->innerJoinWith('brand bFilter', false);

		// подключаем таблицу моделей
		$query->innerJoinWith('modelRel mFilter', false);

		// подключаем таблицу вариаций
		$query->innerJoinWith('variation vFilter', false);

		$user = \Yii::$app->user;

		/**
		 * @var Region $region
		 */

		$region = \Yii::$app->region->current;

		// фильтр наличия
		$query->innerJoinWith(['zonePrice' => function (ActiveQuery $q) use ($region) {

			$q
				->alias('zpFilter')
				->andOnCondition([
					'and',
					['zpFilter.zone_id' => $region->getPriceZoneId()],
					[
						'or',
						['zpFilter.preorder' => ZonePrice::PREORDER],
						['>', 'zpFilter.total_amount', 0],
					],
				]);

		}], false);

		// Фильтр по sale
		if (isset($params['sale']) && (int)$params['sale'] > 0)
			$query->bySales();

		// Только в наличии в магазинах
		if (isset($params['shop']) && !empty($params['shop'])) {

			$shopQuery = ShopStock::find()
				->alias('ss')
				->select(['ss.item_idx idx', 'SUM(ss.amount) qty'])
				->innerJoinWith(['shop' => function (ActiveQuery $q) use ($region) {
					$q->alias('s')
						->andOnCondition('s.region_id=:regionId AND s.not_show!=:notShow AND s.shop_id>0 AND s.is_active=:isActive', [
							':regionId' => $region->getRegionIdForShops(),
							':notShow' => Shop::NOT_SHOW,
							':isActive' => Shop::IS_ACTIVE,
						]);
				}], false)
				->groupBy(['ss.item_idx'])
				->having('qty>0')
				->andWhere(['s.url' => $params['shop']]);

			$query->innerJoin(['sav' => $shopQuery], 'g.disk_id=sav.idx');

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

			$query->joinWith(['autoRel' => function (ActiveQuery $q) use ($params) {
				$q
					->alias('ad')
					->joinWith(['autoModification' => function (ActiveQuery $q) use ($params) {

						$q
							->alias('ada')
							->andWhere(['ada.modification_slug' => $params['auto']]);

					}], false);

			}], false);
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
		$query->with(['brand', 'modelRel', 'variation.color', 'variation.model.brand']);

		/**
		 * @var OptUser $userIdentity
		 */
		$userIdentity = \Yii::$app->user->getIdentity();

		// это ленивая загрузка для прайсов и наличия
		$query
			->withPricesByRegion($userIdentity->region)
			->withStockByOrderTypeGroup($userIdentity);

		// и все это в датапровайдер
		$dataProvider = new TyreGoodDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => [
					'default' => SORT_ASC,
				],
				'attributes' => [
					'default' => [
						'asc' => [
							'bFilter.pos' => SORT_ASC,
							'mFilter.sortorder' => SORT_ASC,
							'mFilter.title' => SORT_ASC,
							'zpFilter.price' => SORT_ASC,
						],
						'desc' => [
							'bFilter.pos' => SORT_DESC,
							'mFilter.sortorder' => SORT_DESC,
							'mFilter.title' => SORT_DESC,
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
