<?php

namespace api\modules\vendor\modules\nokian\components\responces;

use api\modules\vendor\modules\nokian\components\Order;
use api\modules\vendor\modules\nokian\models\forms\OrderCancelForm;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;

class OrderCancelled extends Component implements Arrayable
{

	use ArrayableTrait;
	/**
	 * @var OrderCancelForm
	 */
	protected $orderCancelForm;

	public function __construct(OrderCancelForm $orderForm, array $config = [])
	{
		parent::__construct($config);
		$this->orderCancelForm = $orderForm;
	}

	public function fields()
	{
		return [
			'order-status' => 'status',
			'reason',
			'partner-order-id' => 'reservId',
		];
	}

	public function getReason()
	{
		return 'CANCELLED_REFUSAL';
	}

	public function getStatus()
	{
		return Order::STATUS_CANCELLED;
	}

	public function getReservId()
	{
		return $this->orderCancelForm->partnerOrderId;
	}

}
