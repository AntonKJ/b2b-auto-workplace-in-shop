<?php

namespace api\modules\vendor\modules\nokian\models\forms;

use api\models\VendorOrder;
use api\models\VendorUser;
use common\components\webService\request\GetOrdersByVendor;
use common\components\webService\response\CancelOrder;
use common\components\webService\response\GetListOrders;
use common\models\OptUser;
use domain\entities\service1c\Order;
use domain\services\Service1c;
use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;

/**
 * Class OrderCancelForm
 * @package api\modules\vendor\modules\nokian\models\forms
 */
class OrderCancelForm extends Model
{

	public $partnerOrderId;
	public $action;
	public $entity;
	public $orderStatus;
	public $reason;

	/**
	 * @var Service1c
	 */
	protected $_service1c;
	/**
	 * @var VendorUser
	 */
	protected $_vendorUser;
	/**
	 * @var OptUser
	 */
	protected $_user;
	/**
	 * @var Order
	 */
	protected $_order;

	public function __construct(VendorUser $user, Service1c $service1c, array $config = [])
	{
		parent::__construct($config);
		$this->_vendorUser = $user;
		$this->_user = $this->_vendorUser->getOptUser();
		$this->_service1c = $service1c;
	}

	public function attributeLabels()
	{
		return [

		];
	}

	public function rules()
	{
		return [

			[['entity'], 'required'],
			[['entity'], 'compare', 'compareValue' => 'ORDER'],

			[['action'], 'required'],
			[['action'], 'compare', 'compareValue' => 'UPDATE'],

			[['partnerOrderId'], 'required'],
			[['partnerOrderId'], 'string', 'max' => 16],
			[['partnerOrderId'], 'validateOrderId'],

			[['orderStatus'], 'required'],
			[['orderStatus'], 'compare', 'compareValue' => 'CANCELLED', 'message' => 'Неизвестный статус `{value}`'],

		];
	}

	protected function getVendorOrderIds(): array
	{
		static $orderIds;
		if ($orderIds === null) {

			$request = new GetOrdersByVendor();
			$request->Vender = $this->_vendorUser->getVendor();

			/**
			 * @var GetListOrders $response
			 */
			$response = Yii::$app->webservice->send($request);
			$orders = $response->getOrders();

			$orderIds = [];
			foreach ($orders as $order) {
				$orderIds[] = $order->Number;
			}
		}

		return $orderIds;
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 */
	public function validateOrderId($attribute, $params, $validator): void
	{
		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}
		$orderNumber = $this->{$attribute};
		if (!in_array($orderNumber, $this->getVendorOrderIds())) {
			$this->addError($attribute, sprintf('Заказ %s не найден.', $orderNumber));
		}
	}

	/**
	 * @return bool
	 * @throws BadRequestHttpException
	 */
	public function cancelOrder(): bool
	{
		if (false === $this->validate()) {
			return false;
		}
		/**
		 * @var CancelOrder $result
		 */
		$result = $this->_service1c->cancelOrder(null, $this->partnerOrderId);
		if ((bool)$result['status'] === true) {

			// Чистим кешь по текущему пользователю
			// TagDependency::invalidate(Yii::$app->cache, ["orders-user-{$this->_user->getId()}"]);

			// Обновляем статус заказа, чтобы не отправлять уведомления в вианор
			$vendorOrder = VendorOrder::find()
				->byVendor($this->_vendorUser)
				->byOrderId($this->partnerOrderId)
				->one();

			if ($vendorOrder === null) {
				$vendorOrder = new VendorOrder();
				$vendorOrder->vendor = $this->_vendorUser->getVendor();
				$vendorOrder->order_id = $this->partnerOrderId;
			}

			$vendorOrder->status = VendorOrder::STATUS_CANCELLED;
			$vendorOrder->notified_status = VendorOrder::STATUS_CANCELLED;

			$vendorOrder->save(false);
		} else {
			throw new BadRequestHttpException($result['message'] ??
				sprintf('В процессе отмены заказа %s, возникла неожиданная ошибка.', $this->partnerOrderId));
		}

		return (bool)$result['status'];
	}

}
