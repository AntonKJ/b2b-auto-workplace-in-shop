<?php

namespace common\components\file\storageStrategy;

class DiskVariationStorageStrategyPreview extends DiskVariationStorageStrategyDefault
{

	public function getPath()
	{
		return $this->getMediaComponent()->getStoragePath(implode(DIRECTORY_SEPARATOR, [
			'catalog',
			'disk',
			$this->brand->url,
			'preview',
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
			'preview',
			$this->getFilename() . '?v=' . $this->brand->images_version,
		]));
	}

}
