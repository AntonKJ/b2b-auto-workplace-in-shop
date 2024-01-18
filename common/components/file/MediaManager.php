<?php

namespace common\components\file;

use yii\base\Component;
use yii\base\InvalidConfigException;

class MediaManager extends Component
{

	/**
	 * @var string|array|FileSystem
	 */
	public $fileSystemComponent;

	/**
	 * @throws InvalidConfigException
	 */
	public function init()
	{

		if ($this->fileSystemComponent === null)
			throw new InvalidConfigException(get_class($this) . '::$fileComponent must be set.');

		if (!$this->fileSystemComponent instanceof FileSystem)
			$this->fileSystemComponent = \Yii::createObject($this->fileSystemComponent);

		parent::init();
	}

	/**
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->fileSystemComponent, $method], $parameters);
	}

}