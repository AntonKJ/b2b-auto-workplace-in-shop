<?php

namespace api\modules\regular\components\ecommerce\repositories;

use api\modules\regular\models\Shop;
use myexample\ecommerce\RegionEntityInterface;
use myexample\ecommerce\ShopCollection;
use myexample\ecommerce\ShopRepositoryInterface;

class ShopRepository implements ShopRepositoryInterface
{

	/**
	 * @return ShopCollection
	 */
	public function getShopsActiveOrderedByGroupShopId(): ShopCollection
	{

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
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return array
	 */
	public function getShopsIdByActiveByNotStorageOnlyByRegionEntity(RegionEntityInterface $region): array
	{

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
	}

	/**
	 * @return array
	 */
	public function getShopsIdByActive(): array
	{

		$shopIds = Shop::find()
			->select('shop_id')
			->active()
			->column();

		return $shopIds;
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return array
	 */
	public function getShopsIdByActiveByRegionEntity(RegionEntityInterface $region): array
	{

		static $cache = [];

		if (!isset($cache[$region->getId()])) {

			$cache[$region->getId()] = Shop::find()
				->select('shop_id')
				->active()
				->byShopIdIsSet()
				->byZoneId($region->getZoneId())
				->column();
		}

		return $cache[$region->getId()];
	}

}