<?php

namespace common\models\query;

use common\interfaces\OrderTypeGroupableInterface;
use common\interfaces\RegionEntityInterface;
use common\models\TyreGood;
use common\models\ZonePrice;
use domain\entities\PriceRange;

/**
 * This is the ActiveQuery class for [[TyreGood]].
 *
 * @see TyreGood
 */
class TyreGoodQuery extends \yii\db\ActiveQuery
{

	private $relationsWith;

	public function withBrandFilter($alias = 'bFilter')
	{

		$scope = __METHOD__;
		if (!isset($this->relationsWith[$scope][$alias])) {

			$this->relationsWith[$scope][$alias] = true;
			$this->innerJoinWith("brand {$alias}", false);
		}

		return $this;
	}

	public function withModelFilter($alias = 'mFilter')
	{

		$scope = __METHOD__;
		if (!isset($this->relationsWith[$scope][$alias])) {

			$this->relationsWith[$scope][$alias] = true;
			$this->innerJoinWith("model {$alias}", false);
		}

		return $this;
	}

	public function withZonePriceFilter(RegionEntityInterface $region, $alias = 'zpFilter')
	{

		$scope = __METHOD__;
		if (!isset($this->relationsWith[$scope][$alias])) {

			$this->relationsWith[$scope][$alias] = true;
			$this->innerJoinWith(['zonePrice' => function (ZonePriceQuery $q) use ($region, $alias) {
				$q
					->alias($alias)
					->byRegionZonePrice($region);
			}], false);
		}

		return $this;
	}

	public function withOrderTypeStockFilter(OrderTypeGroupableInterface $orderTypeGroup, $alias = 'otsFilter')
	{
		$scope = __METHOD__;
		if (!isset($this->relationsWith[$scope][$alias])) {
			$this->relationsWith[$scope][$alias] = true;
			$this->innerJoinWith(['orderTypeStock' => static function (OrderTypeStockQuery $q) use ($orderTypeGroup, $alias) {
				$q
					->alias($alias)
					->byOrderTypeGroup($orderTypeGroup);
			}], false);
		}
		return $this;
	}

	public function availableOnly(string $zonePriceTableAlias = 'zpFilter', string $orderTypeStockTableAlias = 'otsFilter')
	{
		if (!empty($zonePriceTableAlias)) {
			$zonePriceTableAlias .= '.';
		}
		if (!empty($orderTypeStockTableAlias)) {
			$orderTypeStockTableAlias .= '.';
		}
		return $this->andWhere([
			'or',
			['>', "{$orderTypeStockTableAlias}[[amount]]", 0],
			["{$zonePriceTableAlias}[[preorder]]" => ZonePrice::PREORDER],
		]);
	}

	/**
	 * Фильтр по первичному ключу
	 * @param $id
	 * @return $this
	 */
	public function byId($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[idx]]" => $id,
		]);
	}

	/**
	 * Фильтр по коду производителся
	 * @param $code
	 * @return $this
	 */
	public function byManufCode($code)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[manuf_code]]" => $code,
		]);
	}

	/**
	 * Фильтр по характеристике RUNFLAT
	 * @return $this
	 */
	public function byRunflat()
	{

		/**
		 * @var TyreGood $class
		 */
		$class = $this->modelClass;

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[runflat]]" => $class::RUNFLAT,
		]);
	}

	/**
	 * Фильтр по характеристике PINS
	 * @param string $modelTableAlias
	 * @return $this
	 */
	public function byPins($modelTableAlias = 'mFilter')
	{

		/**
		 * @var TyreGood $class
		 */
		$class = $this->modelClass;

		$alias = $this->getAlias();

		$modelTableAliasPrefix = $modelTableAlias;
		if (!empty($modelTableAliasPrefix))
			$modelTableAliasPrefix .= '.';

		return $this
			->withModelFilter($modelTableAlias)
			->andWhere([
				'or',
				["{$alias}.[[pin]]" => $class::PINS_YES],
				["{$modelTableAliasPrefix}[[pin]]" => $class::PINS_YES],
			]);
	}

	/**
	 * Фильтр по сезону
	 * @param $season
	 * @return $this
	 */
	public function bySeason($season)
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[season]]" => $season,
		]);
	}

	/**
	 * Фильтр по рейтингу скорости
	 * @param $speedRating
	 * @return $this
	 */
	public function bySpeedRating($speedRating)
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[cc]]" => $speedRating,
		]);
	}

	/**
	 * Фильтр по url бренда
	 * @param mixed $brandUrl
	 * @param string $brandTableAlias
	 * @return $this
	 */
	public function byBrandUrl($brandUrl, $brandTableAlias = 'bFilter')
	{
		$brandTableAliasPrefix = $brandTableAlias;
		if (!empty($brandTableAliasPrefix))
			$brandTableAliasPrefix .= '.';

		return $this
			->withBrandFilter($brandTableAlias)
			->andWhere([
				"{$brandTableAliasPrefix}[[url]]" => $brandUrl,
			]);
	}

	/**
	 * Фильтр по url модели шины
	 * @param mixed $modelUrl
	 * @param string $modelTableAlias
	 * @return $this
	 */
	public function byModelUrl($modelUrl, $modelTableAlias = 'mFilter')
	{
		$modelTableAliasPrefix = $modelTableAlias;
		if (!empty($modelTableAliasPrefix))
			$modelTableAliasPrefix .= '.';

		return $this
			->withModelFilter($modelTableAlias)
			->andWhere([
				"{$modelTableAliasPrefix}[[url]]" => $modelUrl,
			]);
	}

	/**
	 * Фильтр по прайсу
	 * @param PriceRange $priceRange
	 * @param string $zonePriceTableAlias
	 * @return $this
	 */
	public function byPriceRange(PriceRange $priceRange, $zonePriceTableAlias = 'zpFilter')
	{

		if (!empty($zonePriceTableAlias))
			$zonePriceTableAlias .= '.';

		if (null !== $priceRange->getFrom() && null !== $priceRange->getTo())
			$this->andWhere(['between', "{$zonePriceTableAlias}[[price]]", $priceRange->getFrom(), $priceRange->getTo()]);
		elseif (null !== $priceRange->getFrom())
			$this->andWhere(['>=', "{$zonePriceTableAlias}[[price]]", $priceRange->getFrom()]);
		elseif (null !== $priceRange->getTo())
			$this->andWhere(['<=', "{$zonePriceTableAlias}[[price]]", $priceRange->getTo()]);

		return $this;
	}

	/**
	 * Фильтр по распродажамs
	 * @param string $zonePriceTableAlias
	 * @return $this
	 */
	public function bySales($zonePriceTableAlias = 'zpFilter')
	{

		if (!empty($zonePriceTableAlias))
			$zonePriceTableAlias .= '.';

		$this->andWhere(["{$zonePriceTableAlias}[[sale]]" => ZonePrice::SALE]);

		return $this;
	}

	/**
	 * Фильтр по индексу нагрузки
	 * @param $loadIndex
	 * @return $this
	 */
	public function byLoadIndex($loadIndex)
	{

		$alias = $this->getAlias();

		return $this->andWhere([
			"{$alias}.[[in_type]]" => $loadIndex,
		]);
	}

	public function withPricesByRegion(RegionEntityInterface $region, $alias = 'zp')
	{
		return $this->with(['zonePrice' => function (ZonePriceQuery $q) use ($region, $alias) {
			$q
				->alias($alias)
				->byRegionZonePrice($region);
		}]);
	}

	public function withStockByOrderTypeGroup(OrderTypeGroupableInterface $orderTypeGroup, $alias = 'otg')
	{
		return $this->with(['orderTypeStock' => function (OrderTypeStockQuery $q) use ($orderTypeGroup, $alias) {
			$q
				->alias($alias)
				->byOrderTypeGroup($orderTypeGroup);
		}]);
	}

	/**
	 * @inheritdoc
	 * @return TyreGood[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return TyreGood|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
