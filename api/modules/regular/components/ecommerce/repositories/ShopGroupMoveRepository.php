<?php

namespace api\modules\regular\components\ecommerce\repositories;

use api\modules\regular\models\ShopGroupMove;
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

		$reader = ShopGroupMove::find()
			->addOrderBy(['move_id' => SORT_ASC]);

		/** @var ShopGroupMove $shopMove */
		foreach ($reader->each() as $shopMove) {
			$moves->add($shopMove->getEcommerceEntity());
		}

		return $moves;
	}

}