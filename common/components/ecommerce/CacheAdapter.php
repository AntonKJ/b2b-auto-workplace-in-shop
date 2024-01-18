<?php

namespace common\components\ecommerce;

use myexample\ecommerce\CacheInterface;

class CacheAdapter implements CacheInterface
{

	/**
	 * @var \yii\caching\CacheInterface
	 */
	protected $cache;

	public function __construct(\yii\caching\CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * @param $key
	 * @return string
	 */
	public function buildKey($key)
	{
		return $this->cache->buildKey($key);
	}

	public function get($key)
	{
		return $this->cache->get($key);
	}

	public function multiGet($keys)
	{
		return $this->cache->multiGet($keys);
	}

	public function set($key, $value, $duration = null)
	{
		return $this->cache->set($key, $value, $duration);
	}

	public function multiSet($items, $duration = 0)
	{
		return $this->cache->multiSet($items, $duration);
	}

}
