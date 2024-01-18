<?php

namespace common\components\deliveries;

use common\components\Delivery;
use common\interfaces\B2BUserInterface;
use common\interfaces\RegionEntityInterface;
use common\models\OptUser;
use yii\base\ArrayableTrait;
use yii\base\Component;
use yii\base\UnknownMethodException;

abstract class DeliveryAbstract extends Component implements DeliveryInterface
{

	use ArrayableTrait;

	/**
	 * @var DeliveryGoodCollection
	 */
	protected $goods;

	protected $_user;
	protected $_region;

	public function __construct(RegionEntityInterface $region, B2BUserInterface $user, DeliveryGoodCollection $goods, array $config = [])
	{
		parent::__construct($config);

		$this->goods = $goods;

		$this->_region = $region;
		$this->_user = $user;
	}

	/**
	 * @return Delivery
	 */
	public function getDeliveryComponent()
	{
		return \Yii::$app->delivery;
	}

	/**
	 * @return B2BUserInterface|OptUser
	 */
	public function getUser()
	{
		return $this->_user;
	}

	public function getRegion()
	{
		return $this->_region;
	}

	public function fields()
	{
		return [
			'category',
			'title',
		];
	}

	public function getDataForClient()
	{
		return $this->getData();
	}

	public function getActiveOrderTypes(): array
	{
		throw new UnknownMethodException('Method not implemented!');
	}

}