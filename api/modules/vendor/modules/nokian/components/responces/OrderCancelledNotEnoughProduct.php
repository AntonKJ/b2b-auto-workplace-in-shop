<?php

namespace api\modules\vendor\modules\nokian\components\responces;

use api\modules\vendor\modules\nokian\components\Order;
use api\modules\vendor\modules\nokian\models\forms\OrderForm;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;

class OrderCancelledNotEnoughProduct extends Component implements Arrayable
{

	use ArrayableTrait;

	protected $orderForm;

	public function __construct(OrderForm $order, array $config = [])
	{
		parent::__construct($config);
		$this->orderForm = $order;
	}

	public function fields()
	{
		return [
			'order-status' => 'status',
			'reason',
			'partner-order-id' => 'reservId',
			'reason-message' => 'reasonMessage',
		];
	}

	public function getReason()
	{
		return 'NOT_ENOUGH_PRODUCT';
	}

	public function getReasonMessage()
	{
		return $this->orderForm->getFirstErrors();
	}

	public function getStatus()
	{
		return Order::STATUS_CANCELLED;
	}

	public function getReservId()
	{
		return '0';
	}

}
