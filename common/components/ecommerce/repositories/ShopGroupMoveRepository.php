<?php

namespace common\components\ecommerce\repositories;

use common\models\ShopGroupMoves;
use myexample\ecommerce\ShopGroupMoveCollection;
use myexample\ecommerce\ShopGroupMoveRepositoryInterface;

class ShopGroupMoveRepository implements ShopGroupMoveRepositoryInterface
{

	/**
	 * @return ShopGroupMoveCollection
	 */
	public function getMoves(): ShopGroupMoveCollection
	{
		$moves = new ShopGroupMoveCollection();
		$reader = ShopGroupMoves::find()
			->addOrderBy(['move_id' => SORT_ASC]);
		/** @var ShopGroupMoves $shopMove */
		foreach ($reader->each() as $shopMove) {
			$moves->add($shopMove->getEcommerceEntity());
		}
		return $moves;
	}

}
