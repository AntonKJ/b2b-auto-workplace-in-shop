<?php

namespace common\components\file\storageStrategy;

use common\models\ShopNetwork;
use yii\base\Component;

class ShopNetworkStorageStrategyDefault extends Component implements StorageStrategyInterface
{

	public $network;

	public function __construct(ShopNetwork $network, array $config = [])
	{

		parent::__construct($config);
		$this->network = $network;
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
			$this->network->logo_url,
		]));
	}

	public function getUrl()
	{
		return $this->getMediaComponent()->getStorageUrl(implode('/', [
			$this->network->logo_url,
		]));
	}

}