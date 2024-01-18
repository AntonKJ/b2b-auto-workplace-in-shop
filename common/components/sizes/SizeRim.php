<?php

namespace common\components\sizes;

/**
 * Class SizeRim
 * @package common\components\sizes
 * @deprecated
 */
class SizeRim extends SizeAbstract
{

	public $diameter;
	public $width;

	public $pn;
	public $pcd;
	public $pcd2;

	public $et;
	public $cb;

	public static function createFromString($str)
	{

		$size = static::parseString($str);
		return new static(array_merge(['parts' => $size['parts']], $size['sizes']));
	}

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
		   #(?<PCD>\b(?:[pb]cd\s*)?[34568](?&XSEP)(?:98|[12]\d{2})(?&MARGIN)\b)
		   #(?<ET>(?:(?:et|ет)\s*)?\-?\d+(?&MARGIN)\b)
		   #(?<CB>\b(?:(?:цо|cb|d(?:ia)?)\s*)?\d{2,3}(?&MARGIN)\b)
		)
		# End defining
		(?:
			(?<diam>(?&DIAM))
			|
			\b(?:[pb]cd\s*)?(?<pn>[34568])(?&XSEP)(?<pcd>(?:98|[12]\d{2})(?&MARGIN))\b
			#(?<pcd>(?&PCD))
			|
			(?:(?:et|ет)\s*)?(?<et>\-?\d+(?&MARGIN))\b
			#(?<et>(?&ET))
			|
			\b(?:(?:цо|cb|d(?:ia)?)\s*)?(?<cb>\d{2,3}(?&MARGIN))\b
			#(?<cb>(?&CB))
		)
		/uixms', $str, $matches);

		$sizes = array_fill_keys(['diameter', 'width', 'pn', 'pcd', 'et', 'cb'], null);

		if ((int)$result > 0 && isset($matches[0]) && [] !== $matches[0])
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

		$sizes = array_map(function ($v) {
			return is_array($v) && ($v = (float)reset($v)) != 0 ? $v : null;
		}, $sizes);

		return [
			'parts' => $matches[0],
			'sizes' => $sizes,
		];
	}

	public function format()
	{
		return "{$this->width}x{$this->diameter} PCD {$this->pn}x{$this->pcd} ET {$this->et} CB {$this->cb}";
	}

}