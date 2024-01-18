<?php

namespace common\models\sphinx;

use common\interfaces\OrderTypeGroupableInterface;
use common\interfaces\RegionEntityInterface;
use common\models\AutoModification;
use common\models\AutoTyre;
use common\models\OrderTypeGroup;
use common\models\query\AutoModificationQuery;
use domain\entities\PriceRange;
use domain\entities\SizeDisk;
use domain\entities\SizeTyre;
use Yii;
use yii\db\Expression;
use yii\sphinx\ActiveQuery;
use function is_array;

class GoodQuery extends ActiveQuery
{

	public function byQ(array $words)
	{

		if ($words === []) {
			return $this;
		}

		$sphinx = $this->getConnection();

		$words = array_map(static function ($v) use ($sphinx) {

			$v = $sphinx->escapeMatchValue($v);
			return "*{$v}*";
		}, $words);

		$expression = new Expression(':match', [
			':match' => '(' . implode(') & (', $words) . ')',
		]);

		$this->match($expression);

		return $this;
	}

	/**
	 * Фильтрация по типу товара
	 * @param $type
	 * @return $this
	 */
	public function byType($type)
	{
		return $this->andWhere(['type' => $type]);
	}

	/**
	 * Фильтрация по ID товара
	 * @param integer|array $id
	 * @return $this
	 */
	public function byId($id)
	{
		return $this->andWhere(['good_id' => $id]);
	}

	/**
	 * Фильтрация по признаку распродажи
	 * @return $this
	 */
	public function bySales()
	{
		$this
			->addSelect(new Expression('INTEGER(order_type_group.sale) AS sale'))
			->andWhere(new Expression('sale = :sale'), [':sale' => GoodTyre::SALE]);

		return $this;
	}

	/**
	 * Фильтрация по признаку технологии RUNFLAT
	 * @return $this
	 */
	public function byRunflat()
	{
		$this
			->addSelect(new Expression('model_params.runflat AS runflat'))
			->andWhere(['runflat' => GoodTyre::RUNFLAT]);

		return $this;
	}

	/**
	 * Фильтрация по автомобилю
	 * @param $autoSlug
	 * @return $this
	 */
	public function byAutoSlug($autoSlug)
	{
		$tyreSizes = AutoTyre::find()
			->select(['sz'])
			->innerJoinWith([
				'autoModification' => static function (AutoModificationQuery $q) use ($autoSlug) {
					$q
						->alias('am')
						->findBySlug($autoSlug);
				},
			])
			->column();

		$diskAutoModel = AutoModification::find()
			->select(new Expression('crc32([[automodel_code_1c]])'))
			->findBySlug($autoSlug)
			->scalar();

		$select = [];
		$params = [];

		if ($diskAutoModel !== false && !empty($diskAutoModel)) {

			$select[] = 'IF(IN(auto_modification,:auto_modification),1,0)';
			$params[':auto_modification'] = (int)$diskAutoModel;
		}

		if ([] !== $tyreSizes) {

			$values = '\'' . implode('\',\'', $tyreSizes) . '\'';

			$select[] = "IF(IN(good_params.size,{$values}),1,0)";
		}

		if ($select !== []) {

			$this
				->addSelect(new Expression('(' . implode('+', $select) . ') AS auto_cond'))
				->andWhere('auto_cond>0');

			if ($params !== []) {
				$this->addParams($params);
			}
		}

		return $this;
	}

	/**
	 * Фильтрация по признаку шипы
	 * @param $pins
	 * @return $this
	 */
	public function byPins($pins)
	{

		$values = implode(',', $pins);
		return $this
			->addSelect(new Expression("IN(model_params.pin,{$values}) AS pin_condition"))
			->andWhere('pin_condition>0');

		//return $this->andWhere(new Expression('model_params.pin = :pin'), [':pin' => GoodTyre::PINS]);
	}

	/**
	 * Фильтрация по признаку не шипы
	 * @return $this
	 * @deprecated use byPins instead
	 */
	public function byPinsNone()
	{
		return $this->andWhere(new Expression('model_params.pin = :pin'), [':pin' => GoodTyre::PINS_NONE]);
	}

	/**
	 * Фильтрация по сезоности
	 * @param $season
	 * @return $this
	 */
	public function bySeason($season)
	{

		$values = '\'' . mb_strtolower(implode('\',\'', $season)) . '\'';
		$this
			->addSelect(new Expression("IN(model_params.season,{$values}) AS season_condition"))
			->andWhere('season_condition>0');

		return $this;
	}

	/**
	 * Фильтрация по рейтингу скорости
	 * @param $rating
	 * @return $this
	 */
	public function bySpeedRating($rating)
	{

		$values = '\'' . implode('\',\'', $rating) . '\'';
		$this
			->addSelect(new Expression("IN(good_params.speed_rating,{$values}) AS speed_rating_condition"))
			->andWhere('speed_rating_condition>0');

		return $this;
	}

	/**
	 * Фильтрация по индексу нагрузки
	 * @param $index
	 * @return $this
	 */
	public function byLoadIndex($index)
	{
		$index = array_map(static function ($v) {
			return (string)trim($v);
		}, $index);

		$values = '\'' . implode('\',\'', $index) . '\'';
		$this
			->addSelect(new Expression("IN(good_params.load_index,{$values}) AS load_index_condition"))
			->andWhere('load_index_condition>0');

		return $this;
	}

	/**
	 * Фильтрация по слагу бренда
	 * @param $slug
	 * @return $this
	 */
	public function byBrandSlug($slug)
	{
		return $this->andWhere(['brand_slug' => $slug]);
	}

	/**
	 * Фильтрация по id бренда
	 * @param $id
	 * @return $this
	 */
	public function byBrandId($id)
	{
		return $this->andWhere(['brand_id' => $id]);
	}

	/**
	 * Фильтрация по слагу модели
	 * @param $slug
	 * @return $this
	 */
	public function byModelSlug($slug)
	{
		return $this->andWhere(['model_slug' => $slug]);
	}

	/**
	 * Фильтрация по id модели
	 * @param $id
	 * @return $this
	 */
	public function byModelId($id)
	{
		return $this->andWhere(['model_id' => $id]);
	}

	/**
	 * Фильтрация по id вариации
	 * @param $id
	 * @return $this
	 */
	public function byVariationId($id)
	{

		if (is_array($id)) {
			$id = array_map(function ($v) {
				return (int)$v;
			}, $id);
		}

		return $this
			->addSelect(new Expression('variation_params.id AS variation_id'))
			->andWhere(['variation_id' => $id]);
	}

	/**
	 * Фильтрация по slug'у вариации
	 * @param $slug
	 * @return $this
	 */
	public function byVariationSlug($slug)
	{
		return $this
			->addSelect(new Expression('variation_params.slug AS variation_slug'))
			->andWhere(['variation_slug' => $slug]);
	}

	/**
	 * Фильтрация по id расцветки
	 * @param $id
	 * @return $this
	 */
	public function byColorId($id)
	{

		if (is_array($id)) {
			$id = array_map(function ($v) {
				return (int)$v;
			}, $id);
		}

		return $this
			->addSelect(new Expression('variation_params.color.id AS color_id'))
			->andWhere(['color_id' => $id]);
	}

	/**
	 * Фильтрация по slug'у расцветки
	 * @param $slug
	 * @return $this
	 */
	public function byColorSlug($slug)
	{
		return $this
			->addSelect(new Expression('variation_params.color.slug AS color_slug'))
			->andWhere(['color_slug' => $slug]);
	}

	/**
	 * Фильтрация по id типа (материала)
	 * @param $id
	 * @return $this
	 */
	public function byMaterialId($id)
	{

		if (is_array($id)) {
			$id = array_map(function ($v) {
				return (int)$v;
			}, $id);
		}

		return $this
			->addSelect(new Expression('model_params.material.id AS material_id'))
			->andWhere(['material_id' => $id]);
	}

	/**
	 * Фильтрация по slug'у типа (материала)
	 * @param $slug
	 * @return $this
	 */
	public function byMaterialSlug($slug)
	{
		return $this
			->addSelect(new Expression('model_params.material.slug AS material_slug'))
			->andWhere(['material_slug' => $slug]);
	}

	/**
	 * Фильтрация по id магазинов
	 * @param $shopIds
	 * @return $this
	 */
	public function byShopId($shopIds)
	{
		if (!is_array($shopIds)) {
			$shopIds = [$shopIds];
		}

		if ([] !== $shopIds) {

			$prevIds = null;
			foreach ($shopIds as $id) {

				$id = "INTEGER(order_type_group.shops['{$id}'])";
				if ($prevIds !== null) {
					$id = "({$prevIds} + {$id})";
				}

				$prevIds = $id;
			}

			if ($prevIds !== null) {
				$this->addSelect(['selected_shops_amount' => $prevIds]);
			}
		}

		$this
			->addSelect(new Expression('IN(order_type_group.shops_id, ' . implode(',', $shopIds) . ') AS shops_cond'))
			->andWhere('shops_cond>0');

		return $this;
	}

	/**
	 * Фильтрация по размерам
	 * @param $sizes
	 * @return $this
	 */
	public function bySizeTyre($sizes)
	{

		[$sizesSelect, $params] = static::prepareSizesParamsTyre($sizes);

		if ([] !== $sizesSelect) {

			foreach ($sizesSelect as $k => $size) {
				$this->addSelect(new Expression("{$size} AS {$k}"));
			}

			$this
				->addSelect(new Expression('(' . implode(' + ', array_keys($sizesSelect)) . ') size_or_cond'))
				->andWhere('size_or_cond>0')
				->addParams($params);
		}

		return $this;
	}

	public static function prepareSizesParamsTyre($sizes)
	{

		if (!is_array($sizes)) {
			$sizes = [$sizes];
		}

		$sizesSelect = [];
		$params = [];

		foreach ($sizes as $k => $szItm) {

			$select = [];

			$width = $szItm->getWidth();
			if (null !== $width && mb_strlen($width) > 0) {

				$paramKey = ":width_{$k}";

				$select[] = "good_params.width={$paramKey}";
				$params[$paramKey] = $width;
			}

			$radius = $szItm->getRadius();
			if (null !== $radius && mb_strlen($radius) > 0) {

				$paramKey = ":radius_{$k}";

				$select[] = "good_params.radius={$paramKey}";
				$params[$paramKey] = $radius;
			}

			$profile = $szItm->getProfile();
			if (null !== $profile && mb_strlen($profile) > 0) {

				$paramKey = ":profile_{$k}";

				$select[] = "good_params.profile={$paramKey}";
				$params[$paramKey] = $profile;
			}

			if ($szItm->isCommerce()) {

				$paramKey = ":commerce_{$k}";

				$select[] = "good_params.commerce={$paramKey}";
				$params[$paramKey] = GoodTyre::IS_COMMERCE;
			}

			if ($select === []) {
				continue;
			}

			$select = implode(' AND ', $select);
			$sizesSelect["size_{$k}"] = "IF({$select},1,0)";
		}

		return [$sizesSelect, $params];
	}

	/**
	 * Фильтрация по размерам
	 * @param SizeDisk $size
	 * @return $this
	 */
	public function bySizeDisk(SizeDisk $size)
	{

		$diameter = $size->getDiameter();
		if (null !== $diameter && mb_strlen($diameter) > 0) {

			$paramKey = ':diameter';
			$this->andWhere(new Expression("good_params.diameter={$paramKey}"), [
				$paramKey => $diameter,
			]);
		}

		$width = $size->getWidth();
		if (null !== $width && mb_strlen($width) > 0) {

			$paramKey = ':width';
			$this->andWhere(new Expression("good_params.width={$paramKey}"), [
				$paramKey => $width,
			]);
		}

		$pn = $size->getPn();
		if (null !== $pn && mb_strlen($pn) > 0) {

			$paramKey = ':pn';
			$this->andWhere(new Expression("good_params.pn={$paramKey}"), [
				$paramKey => $pn,
			]);
		}

		$pcd = $size->getPcd();
		if (null !== $pcd && mb_strlen($pcd) > 0) {

			$paramKey = ':pcd';
			$this->andWhere(new Expression("good_params.pcd={$paramKey}"), [
				$paramKey => $pcd,
			]);
		}

		$et = $size->getEt();
		if (null !== $et && mb_strlen($et) > 0) {

			$paramKey = ':et';
			$this->andWhere(new Expression("good_params.et={$paramKey}"), [
				$paramKey => $et,
			]);
		}

		$cb = $size->getCb();
		if (null !== $cb && mb_strlen($cb) > 0) {

			$paramKey = ':cb';
			$this->andWhere(new Expression("good_params.cb={$paramKey}"), [
				$paramKey => $cb,
			]);
		}

		return $this;
	}

	public function getSparModelIdsBySizesTyre($sizes)
	{

		[$sizesSelect, $params] = static::prepareSizesParamsTyre($sizes);

		if ([] === $sizesSelect) {
			return [];
		}

		foreach ($sizesSelect as $k => $size) {
			$this->addSelect(new Expression("SUM({$size}) AS {$k}"));
		}

		$this
			->addSelect(new Expression('(' . implode(' + ', array_keys($sizesSelect)) . ') size_or_cond'))
			->andWhere('size_or_cond>0')
			->addParams($params);

		$sizesSelect = array_map(static function ($v) {
			return "{$v}>0";
		}, array_keys($sizesSelect));

		$sizesSelect = implode(' AND ', $sizesSelect);

		$this
			->addSelect([new Expression("IF({$sizesSelect},1,0) AS size_spar_cond")])
			->groupBy('model_id')
			->addSelect('model_id')
			->limit(1000);

		$reader = Yii::$app->sphinx
			->createCommand("SELECT * FROM ({$this->createCommand()->rawSql}) ORDER BY `size_spar_cond` DESC", $this->params)
			->query();

		$modelIds = [];
		foreach ($reader as $row) {

			if ($row['size_spar_cond'] == 0) {
				break;
			}

			$modelIds[] = $row['model_id'];
		}

		return $modelIds;
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return $this
	 */
	public function byRegionZonePrice(RegionEntityInterface $region)
	{
		$this
			->andWhere(['[[zone_id]]' => $region->getPriceZoneId()]);

		return $this;
	}

	/**
	 * @param OrderTypeGroupableInterface $orderTypeGroup
	 * @return $this
	 */
	public function byOrderTypeGroup(OrderTypeGroupableInterface $orderTypeGroup)
	{
		$this
			->addSelect([
				'order_type_group' => new Expression('order_type_groups[:orderTypeGroupId]'),
				'amount' => new Expression('INTEGER(order_type_group.amount_max)'),
				'price' => new Expression('DOUBLE(order_type_group.price)'),
			])
			->addParams([':orderTypeGroupId' => (string)$orderTypeGroup->getOrderTypeGroupId()])
			->andWhere('order_type_group IS NOT NULL');

		return $this;
	}

	/**
	 * Использовать вместе с фильтром по группе
	 * @param $otIds
	 * @return $this
	 */
	public function byOrderTypeIds($otIds): self
	{
		if ([] !== $otIds) {
			$this
				->addSelect(new Expression('ANY(IN(x.id, ' . implode(',', $otIds) . ') FOR x IN order_type_group.order_types) AS order_type_cond_byids'));
		} else {
			$this
				->addSelect(new Expression('0 AS order_type_cond_byids'));
		}
		$this->andWhere('order_type_cond_byids>0');
		return $this;
	}

	/**
	 * @param array $groups
	 * @return $this
	 * @deprecated
	 */
	public function byOrderTypeGroupIntersect(array $groups)
	{

		$otIds = OrderTypeGroup::calculateOrderTypeGroupIntersect($groups);

		if ([] !== $otIds) {
			$this
				->addSelect(new Expression('ANY(IN(x.id, ' . implode(',', $otIds) . ') FOR x IN order_types) AS order_type_cond'));
		} else {
			$this
				->addSelect(new Expression('0 AS order_type_cond'));
		}

		$this->andWhere('order_type_cond>0');

		return $this;
	}

	/**
	 * Фильтрация по прайсу
	 * @param PriceRange $priceRange
	 * @return $this
	 */
	public function byPriceRange(PriceRange $priceRange)
	{

		if (null !== $priceRange->getFrom() && null !== $priceRange->getTo()) {

			$this->andWhere(['between', 'price', $priceRange->getFrom(), $priceRange->getTo()]);
		} elseif (null !== $priceRange->getFrom()) {

			$this->andWhere(['>=', 'price', $priceRange->getFrom()]);
		} elseif (null !== $priceRange->getTo()) {

			$this->andWhere(['<=', 'price', $priceRange->getTo()]);
		}

		return $this;
	}

	/**
	 * Фильтрация по списку списка параметров
	 * @param array $data
	 * @return $this
	 */
	public function byList(array $data)
	{

		$fieldMapper = [
			'sku' => ['sku', 'sku_1c', 'sku_brand'],
		];

		$rowSelect = [];
		$params = [];

		foreach ($data as $rI => $row) {

			$select = [];

			foreach ($row as $field => $fieldValues) {

				if (!isset($fieldMapper[$field])) {
					continue;
				}

				$fieldValues = array_map(static function ($v) {
					return '\'' . $v . '\'';
				}, $fieldValues);

				foreach ($fieldMapper[$field] as $fieldName) {

					$fieldParamKey = ":fp_{$rI}_{$field}_{$fieldName}";

					$select[] = "IF(IN({$fieldName}," . implode(',', $fieldValues) . '),1,0)';
				}
			}

			if ($select === []) {
				continue;
			}

			$rowSelect[] = '(' . implode('+', $select) . ')';
		}

		if ($rowSelect !== []) {
			$this
				->addSelect(new Expression('(' . implode('+', $rowSelect) . ') as list_cond'))
				->addParams($params)
				->andWhere('list_cond>0');
		}

		return $this;
	}

	public function byHideFromUser($userIds)
	{

		if (!is_array($userIds)) {
			$userIds = [$userIds];
		}

		if (is_array($userIds) && [] !== $userIds) {
			$this
				->addSelect(new Expression('IN([[hidden_for_user_id]],' . implode(',', $userIds) . ') hidden_brand_cond'))
				->andWhere('hidden_brand_cond=0');
		}

		return $this;
	}

	public function byTyreSizeCompatible(SizeTyre $size)
	{

		$widthMax = (float)$size->getWidth() + 16;
		$profileFraction = (float)$size->getWidth() * (float)$size->getProfile() / 100;

		$diameterIn = (float)$size->getRadius() * 25.4;
		$diameterOut = $profileFraction * 2 + $diameterIn;

		$length = $diameterOut * 355 / 113;
		$lengthMin = $length - ($length / 100 * 1.3); // -30%
		$lengthMax = $length + ($length / 100 * 1.12); // +12%

		$this
			->addSelect([
				'tLongGoodCompCond' => 'DOUBLE(good_params.tLong)',
				'widthGoodCompCond' => 'DOUBLE(good_params.width)',
			])
			->andWhere([
				'and',
				'tLongGoodCompCond > :tLongMin',
				'tLongGoodCompCond < :tLongMax',
				'widthGoodCompCond < :widthMax',
			])
			->addParams([
				':tLongMin' => $lengthMin,
				':tLongMax' => $lengthMax,
				':widthMax' => $widthMax,
			]);

		return $this;
	}

	public function byHideFromRegion($regionIds)
	{

		if (!is_array($regionIds)) {
			$regionIds = [$regionIds];
		}

		if (is_array($regionIds) && [] !== $regionIds) {
			$this
				->addSelect(new Expression('IN([[hidden_for_region_id]],' . implode(',', $regionIds) . ') hidden_brand_from_region_cond'))
				->andWhere('hidden_brand_from_region_cond=0');
		}

		return $this;
	}

}
