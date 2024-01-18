<?php

namespace domain\entities\service1c;

use DateTimeInterface;
use domain\entities\EntityBase;
use yii\helpers\ArrayHelper;

/**
 * Class SearchOrder
 * @package domain\entities\service1c
 */
class SearchOrder extends EntityBase
{

	const ORDER_TYPE_PICKUP = 'pickup';
	const ORDER_TYPE_DELIVERY = 'delivery';

	protected $number;
	protected $clientName;
	protected $clientCode;
	protected $comment;
	protected $shop;
	protected $date;
	protected $orderType;

	/**
	 * @return array
	 */
	public static function getOrderTypeOptions()
	{
		return [
			static::ORDER_TYPE_PICKUP => 'Самовывоз',
			static::ORDER_TYPE_DELIVERY => 'Доставка',
		];
	}

	/**
	 * @return string|null
	 */
	public function getOrderTypeText()
	{
		return null !== $this->getOrderType() ? ArrayHelper::getValue(static::getOrderTypeOptions(), $this->getOrderType(), null) : null;
	}

	/**
	 * @return mixed
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @return mixed
	 */
	public function getClientName()
	{
		return $this->clientName;
	}

	/**
	 * @return mixed
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @return mixed
	 */
	public function getShop()
	{
		return $this->shop;
	}

	/**
	 * @return mixed
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'number' => $this->getNumber(),
			'clientName' => $this->getClientName(),
			'clientCode' => $this->getClientCode(),
			'comment' => $this->getComment(),
			'shop' => $this->getShop(),
			'date' => ($_d = $this->getDate()) instanceof DateTimeInterface ? $_d->getTimestamp() : null,
			'orderType' => $this->getOrderType(),
			'orderTypeText' => $this->getOrderTypeText(),
		];
	}

	/**
	 * @return string|null
	 */
	public function getClientCode()
	{
		return $this->clientCode;
	}

	/**
	 * @return string|null
	 */
	public function getOrderType()
	{
		return $this->orderType;
	}

}
