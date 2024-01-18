<?php

namespace domain\components;

class MediaManager
{

	/**
	 * @var FileStorageInterface $fileStorage
	 */
	protected $fileStorage;

	/**
	 * MediaManager constructor.
	 * @param FileStorageInterface $fileStorage
	 */
	public function __construct(FileStorageInterface $fileStorage)
	{
		$this->fileStorage = $fileStorage;
	}

	/**
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->fileStorage, $method], $parameters);
	}

}