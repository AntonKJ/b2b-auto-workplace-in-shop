<?php

namespace common\components\file\storageStrategy;

/**
 * @property mixed $path
 * @property mixed $url
 */
class DiskVariationStorageStrategyFace extends DiskVariationStorageStrategyDefault
{

	public function getPath()
	{
		return $this->getMediaComponent()->getStoragePath(implode(DIRECTORY_SEPARATOR, [
			'catalog',
			'disk',
			$this->brand->url,
			'face',
			$this->getFilename(true),
		]));
	}

	public function getUrl()
	{
		if (!file_exists($this->getPath()))
			return $this->getMediaComponent()->getStorageUrl(implode('/', [
				'catalog',
				'disk',
				'notfound.jpg',
			]));

		return $this->getMediaComponent()->getStorageUrl(implode('/', [
			'catalog',
			'disk',
			$this->brand->url,
			'face',
			$this->getFilename(true) . '?v=' . $this->brand->images_version,
		]));
	}

}
