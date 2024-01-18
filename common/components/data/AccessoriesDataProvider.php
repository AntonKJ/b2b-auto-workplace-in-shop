<?php

namespace common\components\data;

use common\components\ecommerce\GoodIdentity;
use common\interfaces\RegionEntityInterface;
use common\models\Autopart;
use myexample\ecommerce\EcommerceInterface;
use myexample\ecommerce\GoodIdentityCollection;
use yii\data\ActiveDataProvider;

class AccessoriesDataProvider extends ActiveDataProvider
{
	/**
	 * @var EcommerceInterface
	 */
	protected $_ecommerce;
	/**
	 * @var RegionEntityInterface
	 */
	protected $_region;
	protected $_availability;

	private $_models;

	public function __construct(EcommerceInterface $ecommerce, RegionEntityInterface $region, $config = [])
	{
		parent::__construct($config);
		$this->_ecommerce = $ecommerce;
		$this->_region = $region;
	}

	public function getModels()
	{
		if ($this->_models === null) {
			$this->_models = parent::getModels();
			if ($this->_availability === null) {
				$this->_availability = $this->prepareAmount($this->getKeys());
			}
			array_walk($this->_models, function (Autopart $v) {
				if (isset($this->_availability[$v->getId()])) {
					$v->setAmount($this->_availability[$v->getId()]);
				}
			});
		}
		return $this->_models;
	}

	protected function prepareAmount(array $keys): array
	{
		$identityCollection = new GoodIdentityCollection();
		foreach ($keys as $id) {
			$identityCollection->add(new GoodIdentity($id));
		}
		return $this->_ecommerce->getAvailability()
			->getMaxAvailableForGoods($identityCollection, $this->_region->getZoneId());
	}

}
