<?php

namespace api\modules\vendor\modules\mosautoshina\components\responces;

use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;

class OrderCanceled extends Component implements Arrayable
{

	use ArrayableTrait;

	protected $orderId;
	protected $response;

	public function __construct($orderId, array $response, array $config = [])
	{
		parent::__construct($config);

		$this->orderId = $orderId;
		$this->response = $response;
	}


	public function fields()
	{
		return [
			'orderId',
			'status',
			'message',
		];
	}

	public function getOrderId()
	{
		return $this->orderId;
	}

	public function getMessage()
	{
		return $this->response['message'];
	}

	public function getStatus()
	{
		return [0 => 'ERROR', 1 => 'OK'][(int)(bool)$this->response['status']];
	}

}