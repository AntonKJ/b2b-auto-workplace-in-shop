<?php

namespace domain\entities;

use domain\interfaces\SizeInterface;

/**
 * Class SizeDisk
 * @package core\entities
 */
class SizeDisk extends EntityBase implements SizeInterface
{

	/**
	 * @var float
	 */
	public $diameter;

	/**
	 * @var float
	 */
	public $width;

	/**
	 * @var int
	 */
	public $pn;

	/**
	 * @var float
	 */
	public $pcd;

	/**
	 * @var float
	 */
	public $pcd2;

	/**
	 * @var float
	 */
	public $et;

	/**
	 * @var float
	 */
	public $cb;

	/**
	 * @var mixed
	 */
	public $parts;

	/**
	 * SizeDisk constructor.
	 * @param float $diameter
	 * @param float $width
	 * @param int $pn
	 * @param float $pcd
	 * @param float $et
	 * @param float $cb
	 * @param float $pcd2
	 * @param mixed $parts
	 */
	public function __construct($diameter, $width, $pn, $pcd, $et, $cb, $pcd2 = null, $parts = [])
	{
		$this->diameter = $diameter;
		$this->width = $width;
		$this->pn = $pn;
		$this->pcd = $pcd;
		$this->pcd2 = $pcd2;
		$this->et = $et;
		$this->cb = $cb;
		$this->parts = $parts;
	}

	/**
	 * @return float
	 */
	public function getDiameter()
	{
		return $this->diameter;
	}

	/**
	 * @return float
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @return int
	 */
	public function getPn()
	{
		return $this->pn;
	}

	/**
	 * @return float
	 */
	public function getPcd()
	{
		return $this->pcd;
	}

	/**
	 * @return float
	 */
	public function getPcd2()
	{
		return $this->pcd2;
	}

	/**
	 * @return float
	 */
	public function getEt()
	{
		return $this->et;
	}

	/**
	 * @return float
	 */
	public function getCb()
	{
		return $this->cb;
	}

	/**
	 * @return mixed
	 */
	public function getParts()
	{
		return $this->parts ?? [];
	}

	/**
	 * @param mixed $parts
	 * @return SizeTyre
	 */
	public function setParts($parts)
	{
		$this->parts = $parts;
		return $this;
	}

	public function format(): string
	{

		$size = [];

		$diameter = [];

		if (!empty($this->diameter))
			$diameter[] = "R{$this->diameter}";

		if (!empty($this->width))
			$diameter[] = "{$this->width}J";

		if ($diameter !== [])
			$size[] = implode(' / ', $diameter);

		$pcd = [];
		if (!empty($this->pn))
			$pcd[] = $this->pn;

		if (!empty($this->pcd))
			$pcd[] = $this->pcd;

		if ($pcd !== [])
			$size[] = 'PCD ' . implode('x', $pcd);

		if (!empty($this->et))
			$size[] = "ЕТ {$this->et}";

		if (!empty($this->cb))
			$size[] = "ЦО {$this->cb}";

		return [] !== $size ? implode(' ', $size) : null;
	}

	public function fields()
	{

		return [
			'diameter' => $this->getDiameter(),
			'width' => $this->getWidth(),
			'pn' => $this->getPn(),
			'pcd' => $this->getPcd(),
			'pcd2' => $this->getPcd2(),
			'et' => $this->getEt(),
			'cb' => $this->getCb(),
		];
	}
}