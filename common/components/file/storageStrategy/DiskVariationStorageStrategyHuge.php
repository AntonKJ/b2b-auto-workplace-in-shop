<?php

namespace common\components\file\storageStrategy;

class DiskVariationStorageStrategyHuge extends DiskVariationStorageStrategyDefault
{

	public function getPath()
	{
		return $this->getMediaComponent()->getStoragePath(implode(DIRECTORY_SEPARATOR, [
			'catalog',
			'disk',
			$this->brand->url,
			'huge',
			$this->getFilename(),
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
			'huge',
			$this->getFilename() . '?v=' . $this->brand->images_version,
		]));
	}

}
