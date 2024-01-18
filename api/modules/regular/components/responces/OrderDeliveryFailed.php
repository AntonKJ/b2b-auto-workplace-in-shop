<?php

namespace api\modules\regular\components\responces;

use api\modules\regular\models\forms\OrderDeliveryForm;
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
class OrderDeliveryFailed extends Component implements Arrayable
{

	use ArrayableTrait;

	protected $orderDeliveryForm;

	public function __construct(OrderDeliveryForm $orderDeliveryForm, array $config = [])
	{
		parent::__construct($config);
		$this->orderDeliveryForm = $orderDeliveryForm;
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
		return $this->orderDeliveryForm->getErrors();
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return 'ERROR';
	}

}
