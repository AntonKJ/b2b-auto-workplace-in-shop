<?php

namespace common\models\sphinx;

use common\components\OrderTypeGroupGenerated;
use common\components\SearchAbstract;
use common\models\OptUser;
use common\models\Region;
use domain\entities\PriceRange;
use domain\entities\SizeDisk;
use Throwable;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\sphinx\ActiveDataProvider;

class GoodDiskSearch extends SearchAbstract
{

	public $q;

	public $addressId;

	public $sku;

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

		if (is_array($value)) {
			$value = array_filter($value, static function ($v) {
				return $v instanceof SizeDisk;
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
	 * @param null $goodPrefix
	 * @return Query
	 */
	protected function prepareListCondition(array $data, $goodPrefix = null)
	{

		$fieldMapper = [
			'sku' => ['code_1c', 'manuf_code'],
		];

		if (!empty($goodPrefix)) {
			$goodPrefix .= '.';
		}

		$queryList = new Query();
		foreach ($data as $listRow) {

			$queryListRow = new Query();
			foreach ($listRow as $field => $fieldValues) {

				if (!isset($fieldMapper[$field])) {
					continue;
				}

				foreach ($fieldMapper[$field] as $fieldName) {
					$queryListRow->orWhere(["{$goodPrefix}[[{$fieldName}]]" => $fieldValues]);
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

		$query = new GoodQuery(GoodDisk::class);

		$query
			->byOrderTypeGroup($intersectedOrderTypeGroup)
			->byHideFromRegion($region->getId());

		$query->byType(GoodDisk::TYPE_DISK);

		if (isset($params['q']) && !empty($params['q'])) {

			// подготавливаем массив
			$prepareWords = $this->prepareWords($params['q']);
			$query->byQ($prepareWords);
		}

		// Фильтр по sale
		if (isset($params['sale']) && (int)$params['sale'] > 0) {
			$query->bySales();
		}

		// Фильтр по прайсу
		if (isset($params['price'])) {
			$query->byPriceRange(new PriceRange($params['price']['from'] ?? null, $params['price']['to'] ?? null));
		}

		if (isset($params['brand']) && !empty($params['brand'])) {
			$query->byBrandSlug($params['brand']);
		}

		if (isset($params['model']) && !empty($params['model'])) {
			$query->byModelSlug($params['model']);
		}

		if (isset($params['variation']) && !empty($params['variation'])) {
			$query->byVariationId($params['variation']);
		}

		if (isset($params['color']) && !empty($params['color'])) {
			$query->byColorId($params['color']);
		}

		if (isset($params['material']) && !empty($params['material'])) {
			$query->byMaterialId($params['material']);
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

		// Фильтр по размерам
		if (isset($params['sizes']) && $params['sizes'] !== []) {

			$size = reset($params['sizes']);
			$query->bySizeDisk($size);
		}

		if (!$user->isGuest) {
			$query->byHideFromUser($user->getId());
		}

		if (!$user->isGuest && isset($params['addressId']) && !empty($params['addressId'])) {
			/** @var OptUser $userIdentity */
			$userIdentity = $user->getIdentity();
			$address = $userIdentity->getAddresses()->cache(3600 * 24 * 30)
				->byHash($params['addressId'])->one();
			$orderTypeIds = $address !== null ? $address->getOrderTypesIds() : [];
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
	 * @throws Throwable
	 */
	public function search($params)
	{

		$this->load($params) && $this->validate();

		$params = $this->getSearchAttributes();

		// формируем запрос к базе
		$query = $this->getSearchQuery($params);

		// ленивая загрузка
		$query->with(['brand', 'model', 'variation.color', 'variation.model.brand']);

		// это нужно для сортировки по полям
		$query->addSelect(new Expression('model_params.type model_type_sort, good_params.diameter good_diameter_sort'));

		$sortorderParams = Yii::$app->cache->getOrSet('disks-sortorder-params', static function () {

			$data = (new \yii\sphinx\Query())
				->select([
					'model_max' => new Expression('MAX(model_sortorder)'),
					'model_type_max' => new Expression('MAX(INTEGER(model_params.type))'),
					'brand_max' => new Expression('MAX(brand_sortorder)'),
				])
				->from('myexample')
				->one();

			$out = [];
			$prevLen = 0;

			foreach ($data as $field => $v) {

				$out[$field] = 10 ** $prevLen;
				$prevLen += mb_strlen((string)$v);
			}

			return array_reverse($out, true);
		}, 0);

		$query->addSelect(new Expression('IF(amount < 4, 0, 1) amount_sort'));
		$query->addSelect(new Expression('(brand_sortorder * :brandMax + INTEGER(model_params.type) * :modelTypeMax + model_sortorder * :modelMax) good_sort', [
			':brandMax' => $sortorderParams['brand_max'] ?? 1,
			':modelTypeMax' => $sortorderParams['model_type_max'] ?? 1,
			':modelMax' => $sortorderParams['model_max'] ?? 1,
		]));

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
							'amount_sort' => SORT_DESC,
							'good_sort' => SORT_ASC,
							'good_diameter_sort' => SORT_ASC,
							'price' => SORT_ASC,
						],
						'desc' => [
							'amount_sort' => SORT_DESC,
							'good_sort' => SORT_DESC,
							'good_diameter_sort' => SORT_DESC,
							'price' => SORT_DESC,
						],
						'default' => SORT_ASC,
					],
					'price' => [
						'asc' => ['price' => SORT_ASC],
						'desc' => ['price' => SORT_DESC],
						'default' => SORT_ASC,
					],
				],
			],
		]);

		return $dataProvider;
	}

	protected function isEmpty($value)
	{
		return $value === '' || $value === [] || $value === null || (is_string($value) && trim($value) === '');
	}
}
