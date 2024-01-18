<?php

namespace common\components\file\storageStrategy;

use common\models\AutoBrand;
use common\models\AutoImage;
use common\models\AutoModel;
use common\models\AutoModification;
use yii\base\Component;

class AutoImageStorageStrategyDefault extends Component implements StorageStrategyInterface
{

	public $autoImage;

	protected $_brand;
	protected $_model;
	protected $_modification;

	protected $_modificationDetails;

	public function __construct(AutoImage $autoImage, array $config = [])
	{
		parent::__construct($config);
		$this->autoImage = $autoImage;
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
			'cars',
			$this->getBrand()->getTitle(),
			implode('_', [
				implode('_', [
					$this->getBrand()->getTitle(),
					$this->getModel()->getTitle(),
					$this->getModificationDetails(),
				]),
				$this->getModification()->getEngineText() . $this->autoImage->photo,
			]),
		]));
	}

	public function getUrl()
	{

		return $this->getMediaComponent()->getStorageUrl(implode('/', [
			'cars',
			$this->getBrand()->getTitle(),
			implode('_', [
				implode('_', [
					$this->getBrand()->getTitle(),
					$this->getModel()->getTitle(),
					$this->getModificationDetails(),
				]),
				$this->getModification()->getEngineText() . $this->autoImage->photo,
			]),
		]));
	}

	/**
	 * @return AutoModification|\yii\db\ActiveQuery
	 */
	public function getModification()
	{
		if ($this->_modification === null)
			$this->_modification = $this->autoImage->modification;

		return $this->_modification;
	}

	/**
	 * @param AutoModification $modification
	 * @return AutoImageStorageStrategyDefault
	 */
	public function setModification(AutoModification $modification)
	{
		$this->_modification = $modification;
		return $this;
	}

	/**
	 * @return array|AutoModel|\yii\db\ActiveRecord
	 */
	public function getModel()
	{

		if ($this->_model === null)
			$this->_model = $this->getModification()->getModel()->one();

		return $this->_model;
	}

	/**
	 * @return array|AutoBrand|\yii\db\ActiveRecord
	 */
	public function getBrand()
	{

		if ($this->_brand === null)
			$this->_brand = $this->getModification()->getBrand()->one();

		return $this->_brand;
	}

	/**
	 * @param mixed $model
	 * @return AutoImageStorageStrategyDefault
	 */
	public function setModel($model)
	{
		$this->_model = $model;
		return $this;
	}

	/**
	 * @param mixed $brand
	 * @return AutoImageStorageStrategyDefault
	 */
	public function setBrand($brand)
	{
		$this->_brand = $brand;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getModificationDetails()
	{

		if ($this->_modificationDetails === null) {
			$this->_modificationDetails = implode('_', [
				$this->getModification()->getRangeText('-'),
			]);
		}

		return $this->_modificationDetails;
	}

}