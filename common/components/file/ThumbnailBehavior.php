<?php

namespace common\components\file;

use common\components\file\presets\PresetInterface;
use yii\base\Behavior;
use yii\base\InvalidParamException;

class ThumbnailBehavior extends Behavior
{

	public $thumbnails;
	public $defaultThumbnail;

	/**
	 * @param string|mixed $thumbnail
	 * @return mixed
	 */
	public function getThumbnail($thumbnail = null)
	{

		if (null === $thumbnail)
			$thumbnail = 'default';

		if ($thumbnail instanceof PresetInterface)
			return $thumbnail->processImage()->target;

		if (!isset($this->thumbnails[$thumbnail])) {
			throw new InvalidParamException("Preset {$thumbnail} not defined");
		}

		$thumbnail = $this->thumbnails[$thumbnail];

		if (isset($thumbnail['source']))
			$thumbnail['source'] = \Yii::createObject($thumbnail['source'], [$this->owner]);

		if (isset($thumbnail['target']))
			$thumbnail['target'] = \Yii::createObject($thumbnail['target'], [$this->owner]);

		$thumbnailObject = new Thumbnail($thumbnail);

		return $thumbnailObject->getImage();
	}

}
