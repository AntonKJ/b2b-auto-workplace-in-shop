<?php


namespace api\modules\regular\components\ecommerce;


use myexample\ecommerce\service1c\Service1cOrder;

class CustomerB2BClient extends \myexample\ecommerce\customers\CustomerB2BClient
{
	public function populateOrder(Service1cOrder $order): Service1cOrder
	{

		$order = parent::populateOrder($order);
		$order->deliveryComment = trim('API; ' . $order->deliveryComment);

		return $order;
	}
}