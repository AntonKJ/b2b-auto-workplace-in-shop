<?php

namespace domain\entities\service1c;

class UserMutualSettlements extends \domain\entities\EntityBase
{

	protected $balance;

	/**
	 * @return mixed
	 */
	public function getBalance()
	{
		return $this->balance;
	}

	/**
	 * @param mixed $balance
	 * @return UserMutualSettlements
	 */
	public function setBalance($balance)
	{
		$this->balance = $balance;
		return $this;
	}


	public function fields()
	{
		return [

			'balance' => $this->getBalance(),

		];
	}

}