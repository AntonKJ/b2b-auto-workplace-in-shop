<?php

namespace common\components\sizes;

/**
 * Class SizeTyre
 * @package common\components\sizes
 * @deprecated
 */
class SizeTyre extends SizeAbstract
{

	public $radius;
	public $profile;
	public $width;
	public $commerce;

	public static function createFromString($str)
	{

		$sizes = static::parseString($str);

		$out = [];
		foreach ($sizes as $size)
			$out[] = new static(array_merge(['parts' => $size['parts']], $size['sizes']));

		return $out;
	}

	public static function parseString($str)
	{

		$result = preg_match_all('/
		# Start defining
		(?(DEFINE)
		   (?<XSEP>\s*[xх\*\-\s\/\.]?\s*)	   
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
		/uixms', $str, $matches);

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
					'sizes' => $size,
				];
			}

		return $sizes;
	}

	public function format()
	{

		$radius = $this->radius;

		if (!empty($radius))
			$radius = 'r' . $radius;

		if ($this->commerce)
			$radius .= 'c';

		$width = $this->width;

		$profile = $this->profile;

		$sizeNormalized = $width . (!empty($profile) ? (!empty($width) ? '/' : '') . $profile : '') . ' ' . $radius;
		$sizeNormalized = trim(trim(preg_replace('/\s{2,}/ui', ' ', $sizeNormalized)), '/');

		return $sizeNormalized;
	}

}