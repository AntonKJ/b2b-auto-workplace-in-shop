<?php

namespace api\modules\regular\components\ecommerce\repositories;

use api\modules\regular\models\ShopStock;
use Exception;
use myexample\ecommerce\GoodIdentityCollection;
use myexample\ecommerce\ShopStockCollection;
use myexample\ecommerce\ShopStockRepositoryInterface;

class ShopStockRepository implements ShopStockRepositoryInterface
{

	/**
	 * @param GoodIdentityCollection $goodIds
	 * @return ShopStockCollection
	 * @throws Exception
	 */
	public function getShopStocksByGoodIds(GoodIdentityCollection $goodIds): ShopStockCollection
	{

		$shopStocks = new ShopStockCollection();

		$reader = ShopStock::find()
			->byGoodId($goodIds->getKeys());

		/** @var ShopStock $shopStock */
		foreach ($reader->each() as $shopStock) {
			$shopStocks->add($shopStock->getEcommerceEntity());
		}

		return $shopStocks;
	}

}