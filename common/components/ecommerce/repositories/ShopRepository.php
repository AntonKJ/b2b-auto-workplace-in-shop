<?php

namespace common\components\ecommerce\repositories;

use common\models\Shop;
use myexample\ecommerce\RegionEntityInterface;
use myexample\ecommerce\ShopCollection;
use myexample\ecommerce\ShopRepositoryInterface;
use Yii;

class ShopRepository implements ShopRepositoryInterface
{

	/**
	 * @return ShopCollection
	 */
	public function getShopsActiveOrderedByGroupShopId(): ShopCollection
	{
		return Yii::$app->getCache()->getOrSet(__METHOD__, static function () {
			$shops = new ShopCollection();
			$query = Shop::find()
				->active()
				->byShopIdIsSet()
				->byGroupIdIsSet()
				->addOrderBy([
					'shopgroup_id' => SORT_ASC,
					'shop_id' => SORT_ASC,
				]);
			/** @var Shop $shopEntity */
			foreach ($query->each() as $shopEntity) {
				$shops->add($shopEntity->getEcommerceEntity());
			}
			return $shops;
		}, 0);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return array
	 */
	public function getShopsIdByActiveByNotStorageOnlyByRegionEntity(RegionEntityInterface $region): array
	{
		return Yii::$app->getCache()->getOrSet(__METHOD__ . '.' . $region->getZoneId(), static function () use ($region) {
			$shopIds = Shop::find()
				->select('shop_id')
				->active()
				->byShopIdIsSet()
				->byGroupIdIsSet()
				->notShow()
				->byZoneId($region->getZoneId())
				->andWhere('shop_id < 10000')
				->column();
			return $shopIds;
		});
	}

	/**
	 * @return array
	 */
	public function getShopsIdByActive(): array
	{
		return Yii::$app->getCache()->getOrSet(__METHOD__, static function () {
			return Shop::find()
				->select('shop_id')
				->active()
				->column();
		}, 0);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return array
	 */
	public function getShopsIdByActiveByRegionEntity(RegionEntityInterface $region): array
	{
		static $cache = [];
		if (!isset($cache[$region->getId()])) {
			$cache[$region->getId()] = Yii::$app->getCache()->getOrSet(__METHOD__ . '.' . $region->getZoneId(), static function () use ($region) {
				return Shop::find()
					->select('shop_id')
					->active()
					->byShopIdIsSet()
					->byZoneId($region->getZoneId())
					->column();
			}, 0);
		}
		return $cache[$region->getId()];
	}

}
