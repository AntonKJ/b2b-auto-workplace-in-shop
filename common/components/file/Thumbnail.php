<?php

namespace common\components\file;

use common\components\file\presets\PresetInterface;
use common\components\file\storageStrategy\StorageStrategyInterface;
use yii\base\Component;

class Thumbnail extends Component
{

	protected $_preset;
	protected $_source;
	protected $_target;

	/**
	 * @return StorageStrategyInterface
	 */
	public function getTarget()
	{
		return $this->_target;
	}

	/**
	 * @param StorageStrategyInterface $targetStorageStrategy
	 */
	public function setTarget(StorageStrategyInterface $targetStorageStrategy)
	{
		$this->_target = $targetStorageStrategy;
	}

	/**
	 * @return StorageStrategyInterface
	 */
	public function getSource()
	{
		return $this->_source;
	}

	/**
	 * @param StorageStrategyInterface $storageStrategy
	 */
	public function setSource(StorageStrategyInterface $storageStrategy)
	{
		$this->_source = $storageStrategy;
	}

	/**
	 * @return PresetInterface
	 */
	public function getPreset()
	{
		return $this->_preset;
	}

	/**
	 * @param PresetInterface $preset
	 */
	public function setPreset($preset)
	{
		$this->_preset = $preset;
	}

	/**
	 * @return StorageStrategyInterface
	 */
	public function getImage()
	{

		// Обрабатываем картинку, если есть прессет
		if (isset($this->_preset)) {

			if (!$this->_preset instanceof PresetInterface)
				$this->_preset = \Yii::createObject($this->_preset, [$this->getSource(), $this->getTarget()]);

			return $this->_preset->processImage();
		}

		return $this->target;
	}

}