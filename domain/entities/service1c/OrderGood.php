<?php

namespace domain\entities\service1c;

class OrderGood extends \domain\entities\EntityBase
{

	public const UNIT_PIECE = 10;

	protected $id;
	protected $title;
	protected $unit;
	protected $amount;
	protected $price;
	protected $priceTotal;
	protected $priceTotalNDS;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	static public function getUnitOptions()
	{
		return [
			static::UNIT_PIECE => 'шт.',
		];
	}

	/**
	 * @return mixed
	 */
	public function getUnit()
	{
		return $this->unit;
	}

	/**
	 * @return mixed
	 */
	public function getUnitText()
	{
		$options = static::getUnitOptions();
		return isset($options[$this->unit]) ? $options[$this->unit] : $this->unit;
	}

	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @return mixed
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @return mixed
	 */
	public function getPriceTotal()
	{
		return $this->priceTotal;
	}

	/**
	 * @return mixed
	 */
	public function getPriceTotalNDS()
	{
		return $this->priceTotalNDS;
	}

	public function fields()
	{
		return [

			'id' => (string)$this->getId(),
			'title' => $this->getTitle(),
			'unit' => $this->getUnit(),
			'unitText' => $this->getUnitText(),
			'amount' => $this->getAmount(),
			'price' => $this->getPrice(),
			'priceTotal' => $this->getPriceTotal(),
			'priceTotalNDS' => $this->getPriceTotalNDS(),

		];
	}

}