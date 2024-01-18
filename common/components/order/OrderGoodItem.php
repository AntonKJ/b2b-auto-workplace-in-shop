<?php

namespace common\components\order;

class OrderGoodItem
{

	const DELEMITER_PARAMS = ":#:";
	const DELEMITER_COL = "\t";

	protected $itemId;
	protected $quantity;
	protected $shopId;
	protected $price;

	/**
	 * OrderGoodItem constructor.
	 * @param string $itemId
	 * @param int $quantity
	 * @param int $shopId
	 * @param null|float $price
	 */
	public function __construct(string $itemId, int $quantity, int $shopId, $price)
	{
		$this->itemId = $itemId;
		$this->quantity = $quantity;
		$this->shopId = $shopId;
		$this->price = $price;
	}

	public function __toString()
	{

		$shopId = $this->shopId;
		if ($shopId > 10000)
			$shopId = '---';

		return implode(static::DELEMITER_COL, [
			'Товар' . static::DELEMITER_PARAMS . str_replace('-', ',', $this->itemId),
			$this->quantity,
			sprintf("%03s", '' . $this->shopId),
			(float)$this->price > 0 ? (float)$this->price : null,
		]);
	}
}