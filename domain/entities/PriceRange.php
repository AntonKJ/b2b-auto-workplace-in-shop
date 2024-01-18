<?php

namespace domain\entities;

/**
 * Class PriceRange
 * @package core\entities
 */
class PriceRange extends EntityBase
{

	protected $from;
	protected $to;

	public function __construct(?float $from, ?float $to)
	{
		$this->from = $from;
		$this->to = $to;
	}

	/**
	 * @return float|null
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @return float|null
	 */
	public function getTo()
	{
		return $this->to;
	}

	public function fields()
	{
		return [
			'from' => $this->from,
			'to' => $this->to,
		];
	}

}