<?php

namespace common\components\file\presets;

use common\components\file\storageStrategy\StorageStrategyInterface;
use yii\base\Component;

abstract class PresetBase extends Component implements PresetInterface
{

	protected $_source;
	protected $_target;

	public function __construct(StorageStrategyInterface $source, StorageStrategyInterface $target, array $config = [])
	{

		parent::__construct($config);

		$this->_source = $source;
		$this->_target = $target;

	}

	public function processImage()
	{
		return $this->_target;
	}

}