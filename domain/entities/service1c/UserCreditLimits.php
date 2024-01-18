<?php

namespace domain\entities\service1c;

class UserCreditLimits extends \domain\entities\EntityBase
{

	protected $limit;
	protected $currency;

	/**
	 * @return mixed
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param mixed $limit
	 * @return UserCreditLimits
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param mixed $currency
	 * @return UserCreditLimits
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
		return $this;
	}

	public function fields()
	{
		return [

			'limit' => $this->getLimit(),
			'currency' => $this->getCurrency(),

		];
	}

}