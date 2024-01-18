<?php

namespace api\modules\vendor\modules\mosautoshina\components\responces;

use api\modules\vendor\modules\mosautoshina\components\Order;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;

class OrderCreated extends Component implements Arrayable
{

	use ArrayableTrait;

	protected $orderId;

	public function __construct($reserv, array $config = [])
	{
		parent::__construct($config);
		$this->orderId = $reserv->id;
	}


	public function fields()
	{
		return [
			'status',
			'orderId',
		];
	}

	public function getReservId()
	{
		return $this->orderId->id;
	}

	public function getStatus()
	{
		return Order::STATUS_IN_RESERVE;
	}

}