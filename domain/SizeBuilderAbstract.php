<?php

namespace domain;

abstract class SizeBuilderAbstract
{

	/**
	 * @var array
	 */
	protected $parts;

	/**
	 * @param $str
	 * @return array
	 */
	abstract static public function parseString($str);

	/**
	 * @param $str
	 * @return array
	 */
	abstract static public function createFromString($str);

	/**
	 * @return array
	 */
	abstract public function getFilledParams();

	abstract public function build();

	public static function instance()
	{
		return new static();
	}

	/**
	 * @param array $parts
	 */
	public function withParts(array $parts)
	{
		$this->parts = $parts;
		return $this;
	}

}