<?php

namespace common\interfaces;

use common\models\query\OrderTypeStockQuery;
use common\models\query\ZonePriceQuery;

interface GoodInterface
{

	public static function getGoodEntityType();

	public static function getGoodMaxAmountInCart(): int;

	public function getPrimaryKey($asArray = false);

	public function getId();

	public function getPrice();

	public function getAmount();

	/**
	 * Goods count in pack
	 * @return int
	 */
	public function getPackSize(): int;

	/**
	 * Quantity add to cart by default
	 * @return int
	 */
	public function getAddToCartQuantity(): int;

	public function getIsPreorder();

	/**
	 * @return ZonePriceQuery
	 */
	public function getZonePrice();

	/**
	 * @return OrderTypeStockQuery
	 */
	public function getOrderTypeStock();

	/**
	 * @return int
	 */
	public function getIsInCart();

}
