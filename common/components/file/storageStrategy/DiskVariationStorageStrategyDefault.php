<?php

namespace common\components\file\storageStrategy;

use common\models\DiskVariation;
use yii\base\Component;

/**
 *
 * @property mixed $path
 * @property string $filename
 * @property mixed $color
 * @property mixed $model
 * @property mixed $brand
 * @property mixed $mediaComponent
 * @property mixed $url
 */
class DiskVariationStorageStrategyDefault extends Component implements StorageStrategyInterface
{

	public $variation;

	protected $_model;
	protected $_brand;
	protected $_color;

	public function __construct(DiskVariation $brand, array $config = [])
	{

		parent::__construct($config);

		$this->variation = $brand;

	}

	/**
	 * @return mixed
	 */
	public function getMediaComponent()
	{
		return \Yii::$app->media;
	}

	/**
	 * @return mixed
	 */
	public function getColor()
	{

		if (!isset($this->_color))
			$this->_color = $this->variation->color;

		return $this->_color;
	}

	/**
	 * @param mixed $color
	 */
	public function setColor($color)
	{
		$this->_color = $color;
	}

	/**
	 * @return mixed
	 */
	public function getBrand()
	{

		if (!isset($this->_brand))
			$this->_brand = $this->model->brand;

		return $this->_brand;
	}

	/**
	 * @param mixed $brand
	 */
	public function setBrand($brand)
	{
		$this->_brand = $brand;
	}

	/**
	 * @return mixed
	 */
	public function getModel()
	{

		static $models = [];

		if (!isset($this->_model))
			$this->_model = $this->variation->model;

		return $this->_model;
	}

	/**
	 * @param mixed $model
	 */
	public function setModel($model)
	{
		$this->_model = $model;
	}

	protected function getFilename($usePng = false)
	{

		$slug = !empty($this->variation->slug_img_old) ? $this->variation->slug_img_old : $this->model->slug;
		return $slug . (!empty($this->variation->color->code) ? '.' . strtolower($this->variation->color->code) : '') . ($usePng ? '.png' : '.jpg');
	}

	public function getPath()
	{
		return $this->getMediaComponent()->getStoragePath(implode(DIRECTORY_SEPARATOR, [
			'catalog',
			'disk',
			$this->brand->url,
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
			$this->getFilename() . '?v=' . $this->brand->images_version,
		]));
	}

}
