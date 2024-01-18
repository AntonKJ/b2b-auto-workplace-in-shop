<?php

namespace common\components;

use yii\caching\MemCache;

class CacheMain extends MemCache
{

	protected function setValues($data, $duration)
	{
		$failedKeys = [];
		foreach ($data as $key => $value) {
			if ($this->setValue($key, $value, $duration) === false) {
				$failedKeys[] = $key;
			}
		}
		return $failedKeys;
	}

}
