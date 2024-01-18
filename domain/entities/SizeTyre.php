<?php

namespace domain\entities;

use domain\interfaces\SizeInterface;

/**
 * Class SizeTyre
 * @package core\entities
 */
class SizeTyre extends EntityBase implements SizeInterface
{

	const COMMERCE_SUFFIX = 'c';

	/**
	 * @var float
	 */
	public $radius;

	/**
	 * @var float
	 */
	public $profile;

	/**
	 * @var float
	 */
	public $width;

	/**
	 * @var bool
	 */
	public $commerce;

	/**
	 * @var array
	 */
	private $parts;

	/**
	 * SizeTyre constructor.
	 * @param float $radius
	 * @param float $profile
	 * @param float $width
	 * @param bool $commerce
	 * @param mixed $parts
	 */
	public function __construct($radius, $profile, $width, $commerce = false, $parts = [])
	{
		$this->radius = $radius;
		$this->profile = $profile;
		$this->width = $width;
		$this->commerce = $commerce;
		$this->parts = $parts;
	}

	/**
	 * @return float|null
	 */
	public function getRadius()
	{
		return $this->radius;
	}

	/**
	 * @return string|null
	 */
	public function getRadiusFormatted()
	{
		$radius = $this->radius;

		if (!empty($radius))
			$radius = 'r' . $radius;

		if ($this->commerce)
			$radius .= static::COMMERCE_SUFFIX;

		return $radius;
	}

	/**
	 * @return float|null
	 */
	public function getProfile()
	{
		return $this->profile;
	}

	/**
	 * @return float|null
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @return bool|null
	 */
	public function isCommerce()
	{
		return $this->commerce;
	}

	/**
	 * @return array
	 */
	public function getParts()
	{
		return $this->parts ?? [];
	}

	/**
	 * @param mixed $parts
	 * @return SizeTyre
	 */
	public function setParts(array $parts)
	{
		$this->parts = $parts;
		return $this;
	}

	public function format(): string
	{

		$radius = $this->getRadiusFormatted();

		$width = $this->width;

		$profile = $this->profile;

		$sizeNormalized = $width . (!empty($profile) ? (!empty($width) ? '/' : '') . $profile : '') . ' ' . $radius;
		$sizeNormalized = trim(trim(preg_replace('/\s{2,}/ui', ' ', $sizeNormalized)), '/');

		return $sizeNormalized;
	}

	public function fields()
	{
		return [
			'width' => $this->getWidth(),
			'profile' => $this->getProfile(),
			'radius' => $this->getRadius(),
			'commerce' => $this->isCommerce(),
		];
	}
}