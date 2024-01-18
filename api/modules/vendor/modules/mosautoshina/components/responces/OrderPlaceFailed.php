<?php

namespace api\modules\vendor\modules\mosautoshina\components\responces;

use api\modules\vendor\modules\mosautoshina\components\Order;
use api\modules\vendor\modules\mosautoshina\models\forms\OrderForm;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;

/**
 *
 * @property string $reason
 * @property mixed $reasonMessage
 * @property null $reservId
 * @property mixed $status
 */
class OrderPlaceFailed extends Component implements Arrayable
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
			'status',
			'reason',
		];
	}

	public function getReason()
	{
		return $this->orderForm->getFirstErrors();
	}

	public function getStatus()
	{
		return Order::STATUS_ERROR;
	}

}