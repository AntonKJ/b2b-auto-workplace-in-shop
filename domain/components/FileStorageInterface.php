<?php

namespace domain\components;

/**
 * FileStorageInterface
 *
 * @author Dev
 */
interface FileStorageInterface
{

	public function getBasePath();

	public function getBaseUrl();

	/**
	 * @param string $suffix path without trailing slash
	 * @return string
	 */
	public function getStoragePath($suffix = null);

	/**
	 * @param string $suffix path without trailing slash
	 * @return string
	 */
	public function getStorageUrl($suffix = null);

}