<?php

namespace domain\entities\car;

use domain\entities\EntityBase;

class ModificationRange extends EntityBase
{

	protected $start;
	protected $end;

	/**
	 * @return mixed
	 */
	public function getStart()
	{
		return $this->start;
	}

	/**
	 * @return mixed
	 */
	public function getEnd()
	{
		return $this->end;
	}

	public function fields()
	{
		return [
			'start' => $this->getStart(),
			'end' => $this->getEnd(),
		];
	}

}