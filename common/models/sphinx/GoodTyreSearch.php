<?php

namespace common\models\sphinx;

use common\components\OrderTypeGroupGenerated;
use common\components\SearchAbstract;
use common\models\OptUser;
use common\models\OptUserAddress;
use common\models\Region;
use domain\entities\PriceRange;
use domain\entities\SizeTyre;
use Throwable;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\sphinx\ActiveDataProvider;
use yii\sphinx\Query;
use function count;
use function is_string;

class GoodTyreSearch extends SearchAbstract
{

	public $q;

	public $sku;

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

	public $addressId;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['q'], 'safe'],

			[['addressId'], 'safe'],

			[['sku'], 'safe'],

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
		if (is_array($value)) {
			$value = array_filter($value, static function ($v) {
				return $v instanceof SizeTyre;
			});
		}
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
			$query = array_filter($query, static function ($v) {
				return mb_strlen($v) >= 2;
			});
		}
		return $query;
	}

	/**
	 * @param array $data
	 * @return Query
	 */
	protected function prepareListCondition(array $data)
	{

		$fieldMapper = [
			'sku' => ['sku', 'sku_1c', 'sku_brand'],
		];

		$queryList = new Query();
		foreach ($data as $listRow) {

			$queryListRow = new Query();
			foreach ($listRow as $field => $fieldValues) {

				if (!isset($fieldMapper[$field])) {
					continue;
				}

				foreach ($fieldMapper[$field] as $fieldName) {
					$queryListRow->orWhere(["[[{$fieldName}]]" => $fieldValues]);
				}
			}

			if (is_array($queryListRow->where) && $queryListRow->where !== []) {
				$queryList->orWhere($queryListRow->where);
			}
		}

		return $queryList;
	}

	/**
	 * @param array $params
	 * @return GoodQuery
	 * @throws Throwable
	 */
	public function getSearchQuery($params = [])
	{

		$user = Yii::$app->user;

		/**
		 * @var Region $region
		 */

		//регион берём из пользователя!!!
		$region = Yii::$app->region->current;

		$intersectedOrderTypeGroup = new OrderTypeGroupGenerated($region, $region, $user->getIdentity());

		$query = new GoodQuery(GoodTyre::class);

		$query
			->byOrderTypeGroup($intersectedOrderTypeGroup)
			->byHideFromRegion($region->getId());

		$query->byType(GoodTyre::TYPE_TYRE);

		if (isset($params['q']) && !empty($params['q'])) {

			// подготавливаем массив
			$prepareWords = $this->prepareWords($params['q']);
			$query->byQ($prepareWords);
		}

		// Фильтр по sale
		if (isset($params['sale']) && (int)$params['sale'] > 0) {
			$query->bySales();
		}

		// Фильтр по flatrun
		if (isset($params['runflat']) && (int)$params['runflat'] > 0) {
			$query->byRunflat();
		}

		// Фильтр по шипам
		if (isset($params['pins']) && !empty($params['pins'])) {
			$query->byPins($params['pins']);
		}

		// Фильтр по прайсу
		if (isset($params['price'])) {
			$query->byPriceRange(new PriceRange($params['price']['from'] ?? null, $params['price']['to'] ?? null));
		}

		if (isset($params['season']) && !empty($params['season'])) {
			$query->bySeason($params['season']);
		}

		if (isset($params['sr']) && !empty($params['sr'])) {
			$query->bySpeedRating($params['sr']);
		}

		if (isset($params['li']) && !empty($params['li'])) {
			$query->byLoadIndex($params['li']);
		}

		if (isset($params['brand']) && !empty($params['brand'])) {
			$query->byBrandSlug($params['brand']);
		}

		if (isset($params['model']) && !empty($params['model'])) {
			$query->byModelSlug($params['model']);
		}

		if (isset($params['shop']) && !empty($params['shop'])) {
			$query->byShopId($params['shop']);
		}

		if (isset($params['auto']) && !empty($params['auto'])) {
			$query->byAutoSlug($params['auto']);
		}

		//todo не работает фильтр #500
		/*		if (isset($params['sku']) && is_array($params['sku']) && [] !== $params['sku']) {

					$skus = array_map(function ($v) {
						return ['sku' => $v];
					}, $params['sku']);

					$query->byList($skus);
				}*/

		// фильтр по списку артикулов и всему такому
		if (isset($params['list']) && is_array($params['list']) && [] !== $params['list']) {
			$query->byList($params['list']);
		}

		// Фильтр по sale
		if (isset($params['sizes']) && $params['sizes'] !== []) {
			if (count($params['sizes']) === 1) {

				[$size] = $params['sizes'];
				$query->bySizeTyre($size);
			} else {

				$modelIds = $wrapQuery = (clone($query))
					->getSparModelIdsBySizesTyre($params['sizes']);

				// Если модели найдены
				if ([] !== $modelIds) {
					$query
						->byModelId($modelIds)
						->bySizeTyre($params['sizes']);
				} else {
					$query
						->addSelect(new Expression('0 AS size_not_found'))
						->andWhere(new Expression('size_not_found=1'));
				}
			}
		}

		if (!$user->isGuest) {
			$query->byHideFromUser($user->getId());
		}

		if (!$user->isGuest && isset($params['addressId']) && !empty($params['addressId'])) {
			/** @var OptUser $userIdentity */
			$userIdentity = $user->getIdentity();

			$addressesReader = $userIdentity->getAddresses()->cache(3600 * 24 * 30)->byHash($params['addressId']);
			/** @var OptUserAddress $address */

			$orderTypeIds = [];
			foreach ($addressesReader->each() as $address) {
				$orderTypeIds[] = $address->getOrderTypesIds();
			}
			if ($orderTypeIds !== []) {
				$orderTypeIds = array_unique(array_merge(...$orderTypeIds));
			}
			$query->byOrderTypeIds($orderTypeIds);
		}


		$query
			->addSelect(['*'])
			->addOptions(['max_matches' => 50000])
			->showMeta(true);

		return $query;
	}

	public function getSearchAttributes()
	{
		return $this->getAttributes($this->safeAttributes());
	}

	/**
	 * @param $params
	 * @return \yii\data\ActiveDataProvider
	 */
	public function search($params)
	{

		$this->load($params) && $this->validate();

		$params = $this->getSearchAttributes();

		// формируем запрос к базе
		$query = $this->getSearchQuery($params);

		// ленивая загрузка
		$query->with(['brand', 'model']);

		$quantitySortorderActive = true;
		if (isset($params['sizes']) && $params['sizes'] !== [] && count($params['sizes']) > 1) {
			$quantitySortorderActive = false;
		}

		// Приоритетная сортировка по текущему сезону и цене
		$query->addSelect(new Expression('(IF(model_params.season = :currentConfigSeason, 0, 1) * 10 + IF(amount >= 4, 0, :quantitySortorderActive)) good_season_sort', [
			':currentConfigSeason' => Yii::$app->global->getSeason(),
			':quantitySortorderActive' => $quantitySortorderActive ? 1 : 0,
		]));

		// это нужно для сортировки по полям
		$query->addSelect(new Expression('model_params.type model_type_sort, good_params.radius good_radius_sort'));

		// Т.к. сфинкс поддерживает сортировку не более чеп по 5 колонкам приходится извращаться
		$query->addSelect(new Expression('(model_sortorder * 100000000 + good_radius_sort * 100000 + price) model_radius_price_order'));

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
							'good_season_sort' => SORT_ASC,
							'WEIGHT()' => SORT_DESC,
							'brand_sortorder' => SORT_ASC,
							'model_type_sort' => SORT_ASC,
							'model_radius_price_order' => SORT_ASC,
						],
						'desc' => [
							'good_season_sort' => SORT_DESC,
							'WEIGHT()' => SORT_DESC,
							'brand_sortorder' => SORT_DESC,
							'model_type_sort' => SORT_DESC,
							'model_radius_price_order' => SORT_DESC,
						],
						'default' => SORT_ASC,
					],
					'price' => [
						'asc' => [
							'price' => SORT_ASC,
							'WEIGHT()' => SORT_DESC,
						],
						'desc' => [
							'price' => SORT_DESC,
							'WEIGHT()' => SORT_DESC,
						],
						'default' => SORT_ASC,
					],
				],
			],
		]);

		return $dataProvider;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	protected function isEmpty($value)
	{
		return $value === '' || $value === [] || $value === null || (is_string($value) && trim($value) === '');
	}
}
