<?php

namespace common\components\file\storageStrategy;

use common\models\DiskBrand;
use yii\base\Component;

class DiskBrandStorageStrategyDefault extends Component implements StorageStrategyInterface
{

	public $brand;

	public function __construct(DiskBrand $brand, array $config = [])
	{

		parent::__construct($config);

		$this->brand = $brand;

	}

	/**
	 * @return mixed
	 */
	public function getMediaComponent()
	{
		return \Yii::$app->media;
	}

	public function getPath()
	{
		return $this->getMediaComponent()->getStoragePath(implode(DIRECTORY_SEPARATOR, [
			'images',
			'logo',
			'wheels',
			$this->brand->logo . ((int)$this->brand->images_version > 0 ? '?' . $this->brand->images_version : ''),
		]));
	}

	public function getUrl()
	{
		return $this->getMediaComponent()->getStorageUrl(implode('/', [
			'images',
			'logo',
			'wheels',
			$this->brand->logo . ((int)$this->brand->images_version > 0 ? '?' . $this->brand->images_version : ''),
		]));
	}

}