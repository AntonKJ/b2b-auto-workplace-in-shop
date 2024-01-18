<?php

namespace domain;

use domain\entities\SizeTyre;

class SizeTyreBuilder extends SizeBuilderAbstract
{

	/**
	 * @var float
	 */
	private $width;

	/**
	 * @var float
	 */
	private $profile;

	/**
	 * @var float
	 */
	private $radius;

	/**
	 * @var bool
	 */
	private $commerce;

	/**
	 * @param $str
	 * @return array
	 */
	static public function parseString($str)
	{

		$result = preg_match_all('@
		# Start defining
		(?(DEFINE)
		   (?<XSEP>\s*[xх\*\-\s\/\._]?\s*)
		   (?<WIDTH>(?:[1-4]\d[05]|(?:[5-9]|[1-4]\d)(?:\.\d{1,2})?))
		   (?<PROFILE>(?:
		        [2-9][05]
		        |
		        (?:
		            (?:[7-9]|1[0-5])(?:\.\d{1,2})?
				)
           ))
		   (?<RADIUS>[123]\d(?:\.\d{1,2})?)
		)
		# End defining
		(?:
			\b
			(?<width>(?&WIDTH)?)(?&XSEP)(?<profile>(?&PROFILE)?)(?&XSEP)[rр]?(?&XSEP)(?<radius>(?&RADIUS)?)(?<commerce>[cс])?
			\b
		)
		@uixms', $str, $matches);

		$sizes = [];
		if ((int)$result > 0 && isset($matches[0]) && [] !== $matches[0])
			foreach ($matches[0] as $key => $part) {

				$part = trim($part);
				if (empty($part))
					continue;

				$size = [
					'width' => ($_v = (float)$matches['width'][$key]) != 0 ? $_v : null,
					'profile' => ($_v = (float)$matches['profile'][$key]) != 0 ? $_v : null,
					'radius' => ($_v = (float)$matches['radius'][$key]) != 0 ? $_v : null,
					'commerce' => !empty($matches['commerce'][$key]) ? true : false,
				];

				if (empty($size['width']) && empty($size['profile']) && empty($size['radius']) && $size['commerce'] == false)
					continue;

				$sizes[] = [
					'parts' => [$part],
					'size' => $size,
				];
			}

		return $sizes;
	}

	/**
	 * @param $str
	 * @return array
	 */
	static public function createFromString($str)
	{

		$sizes = static::parseString($str);

		$out = [];
		foreach ($sizes as $size) {
			$out[] = static::instance()
				->withRadius($size['size']['radius'])
				->withProfile($size['size']['profile'])
				->withWidth($size['size']['width'])
				->withCommerce($size['size']['commerce'])
				->withParts($size['parts'])
				->build();
		}

		return $out;
	}

	/**
	 * @param float $width
	 * @return SizeTyreBuilder
	 */
	public function withWidth(?float $width): SizeTyreBuilder
	{
		$this->width = $width;
		return $this;
	}

	/**
	 * @param float $profile
	 * @return SizeTyreBuilder
	 */
	public function withProfile(?float $profile): SizeTyreBuilder
	{
		$this->profile = $profile;
		return $this;
	}

	/**
	 * @param float $radius
	 * @return SizeTyreBuilder
	 */
	public function withRadius(?float $radius): SizeTyreBuilder
	{
		$this->radius = $radius;
		return $this;
	}

	/**
	 * @param bool $commerce
	 * @return SizeTyreBuilder
	 */
	public function withCommerce(?bool $commerce): SizeTyreBuilder
	{
		$this->commerce = $commerce;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getFilledParams()
	{

		$out = [];

		if (null !== $this->width)
			$out['width'] = $this->width;

		if (null !== $this->radius)
			$out['radius'] = $this->radius;

		if (null !== $this->profile)
			$out['profile'] = $this->profile;

		if (null !== $this->commerce)
			$out['commerce'] = $this->commerce;

		return $out;
	}

	/**
	 * @return SizeTyre
	 */
	public function build(): SizeTyre
	{

		$size = new SizeTyre(
			$this->radius,
			$this->profile,
			$this->width,
			$this->commerce,
			$this->parts ?? []
		);

		return $size;
	}
}