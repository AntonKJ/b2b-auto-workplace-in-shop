<?php

namespace domain\entities\service1c;

use domain\entities\EntityBase;

class OrderSaleNumber extends EntityBase
{

	/**
	 * @var string
	 */
	protected $number;

	/**
	 * @return mixed
	 */
	public function getNumber()
	{
		return $this->number;
	}

	public function fields()
	{
		return [
			'number' => (string)$this->getNumber(),
		];
	}

}