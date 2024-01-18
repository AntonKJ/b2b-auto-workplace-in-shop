<?php

namespace domain\components;

/**
 * FileStorage
 *
 * @author Dev
 */
class FileStorage implements FileStorageInterface
{

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * FileStorage constructor.
	 * @param string $basePath
	 * @param string $baseUrl
	 */
	public function __construct($basePath, $baseUrl)
	{
		$this->basePath = $basePath;
		$this->baseUrl = $baseUrl;
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