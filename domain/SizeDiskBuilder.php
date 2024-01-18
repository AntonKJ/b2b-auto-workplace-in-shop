<?php

namespace domain;

use domain\entities\SizeDisk;

class SizeDiskBuilder extends SizeBuilderAbstract
{

	/**
	 * @var float
	 */
	private $diameter;

	/**
	 * @var float
	 */
	private $width;

	/**
	 * @var int
	 */
	private $pn;

	/**
	 * @var float
	 */
	private $pcd;

	/**
	 * @var float
	 */
	private $pcd2;

	/**
	 * @var float
	 */
	private $et;

	/**
	 * @var float
	 */
	private $cb;

	/**
	 * @param $str
	 * @return array
	 */
	public static function parseString($str)
	{

		$result = preg_match_all('/
		# Start defining
		(?(DEFINE)
		   (?<XSEP>\s*[xх\*\-\s]?\s*)
		   (?<MARGIN>(?:\.\d)?)
		   (?<DIAMETER>r?[12]\d(?&MARGIN))
		   (?<WIDTH>(?:1[01]|[4-9])(?&MARGIN)j?)
		   (?<DIAM>
		     \b
		     (?:
		       (?&DIAMETER)(?:\s*\/\s*(?&WIDTH))?
		       |
		       (?&WIDTH)(?&XSEP)(?&DIAMETER)
		     )
		     \b
		   )
		)
		# End defining
		(?:
			# Диаметр и ширина -------------------------
			(?<diam>(?&DIAM))
			|
			# Разболтовка ------------------------------
			\b(?:[pb]cd\s*)?(?<pn>[34568])(?&XSEP)(?<pcd>(?:98|[12]\d{2})(?&MARGIN))\b
			|
			# Вылет ------------------------------------
			\b
			(?:(?:et|ет)\s*)?
			(?<et>
				(?:
					(?<=et|ет|et\s|ет\s)(?:\-?\d{1,3})
					|
					\-(?:50|[0-4]\d|\d)
					|
					(?:150|[01][0-4]\d|\d{1,2})
				)
				(?&MARGIN)
			)
			\b
			|
			# Центральное отверстие ---------------------
			\b
			(?:(?:цо|cb|d|dia)\s*)?
			(?<cb>
				(?:
					(?<=цо|cb|d|dia|цо\s|cb\s|d\s|dia\s)(?:\s*?(?:\d{1,3}))
					|
					(?:[012]\d{2}|\d{1,2})
				)
				(?&MARGIN)
			)
			\b
		)
		/uixms', $str, $matches);

		$sizes = array_fill_keys(['diameter', 'width', 'pn', 'pcd', 'et', 'cb'], null);

		if ((int)$result > 0 && isset($matches[0]) && [] !== $matches[0]) {

			foreach (['diam', 'pn', 'pcd', 'et', 'cb'] as $part) {

				if (!isset($matches[$part]))
					continue;

				switch (true) {

					case $part == 'diam':

						//todo need optimize and refactoring
						foreach ($matches[$part] as $diam) {

							preg_match('/
							# Start defining
							(?(DEFINE)
							   (?<XSEP>\s*[xх\*\-\s]?\s*)
							   (?<MARGIN>(?:\.\d)?)
							   (?<DIAMETER>[12]\d(?&MARGIN))
							   (?<WIDTH>(?:1[01]|[4-9])(?&MARGIN))
							)
							# End defining
							(?:
							     \b
							     (?:
							       r?(?<diameter_1>(?&DIAMETER))(?:\s*\/\s*(?<width_1>(?&WIDTH))j?)?
							       |
							       (?<width_2>(?&WIDTH))j?(?&XSEP)r?(?<diameter_2>(?&DIAMETER))
							     )
							     \b
							)
							/uixms', $diam, $diamMatching);

							for ($i = 1; $i <= 2; $i++) {

								if (isset($diamMatching["diameter_{$i}"]) && !empty($diamMatching["diameter_{$i}"]))
									$sizes['diameter'][] = $diamMatching["diameter_{$i}"];

								if (isset($diamMatching["width_{$i}"]) && !empty($diamMatching["width_{$i}"]))
									$sizes['width'][] = $diamMatching["width_{$i}"];
							}

						}
						break;

					default:

						$sizes[$part] = array_filter($matches[$part], function ($v) {
							return !empty($v);
						});
				}

			}
		}

		$sizes = array_map(static function ($v) {
			return is_array($v) && ($v = (float)reset($v)) != 0 ? $v : null;
		}, $sizes);

		return [
			[
				'parts' => $matches[0],
				'size' => $sizes,
			],
		];
	}

	/**
	 * @param $str
	 * @return array
	 */
	public static function createFromString($str)
	{

		$sizes = static::parseString($str);

		$out = [];
		foreach ($sizes as $size) {

			$sizeBuilder = static::instance()
				->withDiameter($size['size']['diameter'] ?? null)
				->withWidth($size['size']['width'] ?? null)
				->withPn($size['size']['pn'] ?? null)
				->withPcd($size['size']['pcd'] ?? null)
				->withPcd2($size['size']['pcd2'] ?? null)
				->withEt($size['size']['et'] ?? null)
				->withCb($size['size']['cb'] ?? null)
				->withParts($size['parts'] ?? []);

			if($sizeBuilder->getFilledParams() === [])
				continue;

			$out[] = $sizeBuilder->build();
		}

		return $out;
	}

	/**
	 * @param float $diameter
	 * @return SizeDiskBuilder
	 */
	public function withDiameter(?float $diameter): SizeDiskBuilder
	{
		$this->diameter = $diameter;
		return $this;
	}

	/**
	 * @param float $width
	 * @return SizeDiskBuilder
	 */
	public function withWidth(?float $width): SizeDiskBuilder
	{
		$this->width = $width;
		return $this;
	}

	/**
	 * @param int $pn
	 * @return SizeDiskBuilder
	 */
	public function withPn(?int $pn): SizeDiskBuilder
	{
		$this->pn = $pn;
		return $this;
	}

	/**
	 * @param float $pcd
	 * @return SizeDiskBuilder
	 */
	public function withPcd(?float $pcd): SizeDiskBuilder
	{
		$this->pcd = $pcd;
		return $this;
	}

	/**
	 * @param float $pcd2
	 * @return SizeDiskBuilder
	 */
	public function withPcd2(?float $pcd2): SizeDiskBuilder
	{
		$this->pcd2 = $pcd2;
		return $this;
	}

	/**
	 * @param float $et
	 * @return SizeDiskBuilder
	 */
	public function withEt(?float $et): SizeDiskBuilder
	{
		$this->et = $et;
		return $this;
	}

	/**
	 * @param float $cb
	 * @return SizeDiskBuilder
	 */
	public function withCb(?float $cb): SizeDiskBuilder
	{
		$this->cb = $cb;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getFilledParams()
	{

		$out = [];

		if (null !== $this->diameter)
			$out['diameter'] = $this->diameter;

		if (null !== $this->width)
			$out['width'] = $this->width;

		if (null !== $this->pn)
			$out['pn'] = $this->pn;

		if (null !== $this->pcd)
			$out['pcd'] = $this->pcd;

		if (null !== $this->pcd2)
			$out['pcd2'] = $this->pcd2;

		if (null !== $this->et)
			$out['et'] = $this->et;

		if (null !== $this->cb)
			$out['cb'] = $this->cb;

		return $out;
	}

	/**
	 * @return SizeDisk
	 */
	public function build(): SizeDisk
	{

		$size = new SizeDisk(
			$this->diameter,
			$this->width,
			$this->pn,
			$this->pcd,
			$this->et,
			$this->cb,
			$this->pcd2,
			$this->parts ?? []
		);

		return $size;
	}

}
