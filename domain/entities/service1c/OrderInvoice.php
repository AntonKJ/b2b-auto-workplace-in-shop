<?php

namespace domain\entities\service1c;

class OrderInvoice extends \domain\entities\EntityBase
{

	protected $organization;
	protected $contractor;
	protected $buyer;
	protected $bankBik;
	protected $bankName;
	protected $settlementAccount;
	protected $correspondentAccount;
	protected $accountNumber;
	protected $sum;
	protected $sumNDS;
	protected $sumInWords;
	protected $volume;
	protected $goods;

	/**
	 * @return mixed
	 */
	public function getOrganization()
	{
		return $this->organization;
	}

	/**
	 * @return mixed
	 */
	public function getContractor()
	{
		return $this->contractor;
	}

	/**
	 * @return mixed
	 */
	public function getBuyer()
	{
		return $this->buyer;
	}

	/**
	 * @return mixed
	 */
	public function getBankBik()
	{
		return $this->bankBik;
	}

	/**
	 * @return mixed
	 */
	public function getBankName()
	{
		return $this->bankName;
	}

	/**
	 * @return mixed
	 */
	public function getSettlementAccount()
	{
		return $this->settlementAccount;
	}

	/**
	 * @return mixed
	 */
	public function getCorrespondentAccount()
	{
		return $this->correspondentAccount;
	}

	/**
	 * @return mixed
	 */
	public function getAccountNumber()
	{
		return $this->accountNumber;
	}

	/**
	 * @return mixed
	 */
	public function getSum()
	{
		return $this->sum;
	}

	/**
	 * @return mixed
	 */
	public function getSumNDS()
	{
		return $this->sumNDS;
	}

	/**
	 * @return mixed
	 */
	public function getSumInWords()
	{
		return $this->sumInWords;
	}

	/**
	 * @return mixed
	 */
	public function getVolume()
	{
		return $this->volume;
	}

	/**
	 * @return OrderGood[]
	 */
	public function getGoods()
	{
		return $this->goods;
	}

	public function getInvoiceNumber()
	{
		return md5(serialize($this->fields()));
	}

	public function fields()
	{

		return [

			'organization' => $this->getOrganization(),
			'contractor' => $this->getContractor(),
			'buyer' => $this->getBuyer(),
			'bankBik' => $this->getBankBik(),
			'bankName' => $this->getBankName(),
			'settlementAccount' => $this->getSettlementAccount(),
			'correspondentAccount' => $this->getCorrespondentAccount(),
			'accountNumber' => $this->getAccountNumber(),
			'sum' => $this->getSum(),
			'sumNDS' => $this->getSumNDS(),
			'sumInWords' => $this->getSumInWords(),
			'volume' => $this->getVolume(),
			'goods' => $this->getGoods(),

		];
	}

}