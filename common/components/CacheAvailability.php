<?php

namespace common\components;

class CacheAvailability extends CacheMain
{

	/**
	 * @param mixed $key
	 * @return string
	 */
	public function buildKey($key)
	{
		if (!is_string($key)) {
			$key = md5(serialize($key));
		}
		return $this->keyPrefix . $key;
	}

}
