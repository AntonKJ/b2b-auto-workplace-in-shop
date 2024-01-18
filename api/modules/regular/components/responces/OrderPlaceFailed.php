<?php

namespace api\modules\regular\components\responces;

use api\modules\regular\components\Order;
use api\modules\regular\models\forms\OrderForm;
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

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'status',
			'reason',
		];
	}

	/**
	 * @return array
	 */
	public function getReason()
	{
		return $this->orderForm->getErrors();
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return Order::STATUS_ERROR;
	}

}