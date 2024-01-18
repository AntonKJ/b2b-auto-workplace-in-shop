<?php

namespace common\components\file;

use yii\base\Component;

/**
 * FileComponent
 *
 * @author Dev
 */
class FileSystem extends Component
{

	public $basePath;
	public $baseUrl;
	public $baseDomain;

	public function getBasePath()
	{
		return $this->basePath;
	}

	public function getBaseUrl()
	{
		return $this->baseUrl;
	}

	public function getBaseDomain()
	{
		return $this->baseDomain;
	}

	/**
	 * @param string $suffix path without trailing slash
	 * @return string
	 */
	public function getStoragePath($suffix = null)
	{
		return implode(DIRECTORY_SEPARATOR, [$this->getBasePath(), trim($suffix, DIRECTORY_SEPARATOR)]);
	}

	/**
	 * @param string $suffix path without trailing slash
	 * @return string
	 */
	public function getStorageUrl($suffix = null)
	{
		return implode('/', [$this->getStorageDomain($suffix), trim($suffix, '/')]);
	}

	public function getStorageDomain(string $url): string
	{
		//return '//' . implode('.', ['i' . (crc32($url) % 4), $this->baseDomain]);
		return '//' . implode('.', ['www', $this->baseDomain]);
	}

}
