<?php

namespace domain\entities\service1c;

class ClientDebt extends \domain\entities\EntityBase
{
	/**
	 * Название организации
	 * @var string
	 */
	protected $organisation;
	/**
	 * Договор
	 * @var string
	 */
	protected $object;
	/**
	 * Задолженость
	 * @var float
	 */
	protected $amount;
	/**
	 * Просроченая задолженость
	 * @var float
	 */
	protected $overdueAmount;

	/**
	 * @return string|null
	 */
	public function getOrganisation(): ?string
	{
		return $this->organisation;
	}

	/**
	 * @return string|null
	 */
	public function getObject(): ?string
	{
		return $this->object;
	}

	/**
	 * @return float
	 */
	public function getAmount(): float
	{
		return (float)$this->amount;
	}

	/**
	 * @return float
	 */
	public function getOverdueAmount(): float
	{
		return (float)$this->overdueAmount;
	}

	public function fields(): array
	{
		return [
			'organisation' => $this->getOrganisation(),
			'object' => $this->getObject(),
			'amount' => $this->getAmount(),
			'overdueAmount' => $this->getOverdueAmount(),
		];
	}

}