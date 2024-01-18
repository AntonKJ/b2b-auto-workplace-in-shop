<?php

namespace api\modules\vendor\modules\nokian\components\responces;

use api\modules\vendor\modules\nokian\components\Order;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;

class OrderCreated extends Component implements Arrayable
{

	use ArrayableTrait;

	protected $reserv;

	public function __construct($reserv, array $config = [])
	{
		parent::__construct($config);
		$this->reserv = $reserv;
	}

	public function fields()
	{
		return [
			'order-status' => 'status',
			'partner-order-id' => 'reservId',
		];
	}

	public function getReservId()
	{
		return $this->reserv->id;
	}

	public function getStatus()
	{
		return Order::STATUS_IN_RESERVE;
	}

}
