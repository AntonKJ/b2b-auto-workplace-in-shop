<?php

namespace common\components;

use common\components\shoppingCartMessages\GoodAvailableByPreorder;
use common\components\shoppingCartMessages\GoodNotAvailable;
use common\components\shoppingCartMessages\Messages;
use common\components\shoppingCartMessages\NotEnoughGood;
use common\interfaces\GoodInterface;
use common\models\ShoppingCartGood;
use domain\interfaces\Arrayable;
use yii\base\ArrayableTrait;

class ShoppingCartItem implements Arrayable
{

	use ArrayableTrait;

	/**
	 * @var GoodInterface
	 */
	protected $good;
	protected $item;

	public function __construct(ShoppingCartGood $item, GoodInterface $good = null)
	{
		$this->item = $item;
		$this->good = $good;
	}

	/**
	 * @return GoodInterface|null
	 */
	public function getGood()
	{
		return $this->good;
	}

	/**
	 * @param GoodInterface $good
	 * @return $this
	 */
	public function setGood(GoodInterface $good)
	{
		$this->good = $good;
		return $this;
	}

	/**
	 * @return ShoppingCartGood
	 */
	public function getItem(): ShoppingCartGood
	{
		return $this->item;
	}

	/**
	 * Возвращает кол-во
	 * @return int|null
	 */
	public function getPriceTotal()
	{
		if ($this->getGood() instanceof GoodInterface) {
			return $this->getAmountReal() * $this->getGood()->getPrice();
		}
		return 0.0;
	}

	/**
	 * Возвращает кол-во доступного товара для корзины с учетом предзаказа
	 * @return int|null
	 */
	public function getAmountReal()
	{
		$amount = 0;
		if ($this->getGood() instanceof GoodInterface) {
			$amount = $this->getItem()->quantity;
			if (!$this->getIsPreordered()) {
				$amount = min($amount, $this->getGood()->getAmount());
			}
		}
		return $amount;
	}

	public function getIsPreordered()
	{
		return $this->getGood() instanceof GoodInterface
			&& ((($this->item->quantity) > $this->getGood()->getAmount())
				&& $this->getGood()->getIsPreorder());
	}

	/**
	 * @return Messages
	 */
	public function getMessages()
	{
		$messages = new Messages();
		if ($this->getGood() === null) {
			$messages->add(new GoodNotAvailable());
		}
		if ($this->getGood() instanceof GoodInterface) {
			if (($this->item->quantity) > $this->getGood()->getAmount()) {
				$messages->add(new NotEnoughGood($this->getGood()->getAmount()));
				if ($this->getGood()->getIsPreorder()) {
					$messages->add(new GoodAvailableByPreorder());
				}
			}
		}
		return $messages;
	}

	public function fields()
	{
		return [
			'item' => static function (ShoppingCartItem $model) {
				return $model->getItem();
			},
			'good' => function () {
				return $this->getGood() instanceof GoodInterface ? $this->getGood()->toArray([], ['stock']) : null;
			},
			'quantity' => function () {
				return $this->item->quantity;
			},
			'priceTotal' => function () {
				return $this->getPriceTotal();
			},
			'messages' => function () {
				return $this->getMessages()->toArray();
			},
		];
	}

}
