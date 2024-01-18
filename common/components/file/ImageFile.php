<?php

namespace common\components\file;

use yii\base\Component;

class ImageFile extends Component
{

	public $basePath;
	public $baseUrl;

	public $filename;

	public function getUrl()
	{
		return $this->getStorageUrl($this->filename);
	}

	public function getPath()
	{
		return $this->getStoragePath($this->filename);
	}


	public function getBasePath()
	{
		return $this->basePath;
	}

	public function getBaseUrl()
	{
		return $this->baseUrl;
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
		return implode('/', [$this->getBaseUrl(), trim($suffix, '/')]);
	}

}