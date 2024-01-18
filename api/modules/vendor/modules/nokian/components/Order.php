<?php

namespace api\modules\vendor\modules\nokian\components;

use api\models\VendorOrder;
use api\models\VendorUser;
use api\modules\vendor\modules\nokian\models\forms\OrderForm;
use common\components\deliveries\DeliveryPickup;
use common\components\order\OrderGoodItem;
use common\components\payments\PaymentInterface;
use domain\entities\service1c\OrderReserve;
use domain\services\Service1c;
use Exception;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class Order extends Component
{

	public const STATUS_IN_RESERVE = 'RESERVED';
	public const STATUS_CANCELLED = 'CANCELLED';
	public const STATUS_COMPLETED = 'PERFORMED_ORDER';

	public $shopMapper;

	/**
	 * @return array
	 */
	static function getOrderStatusOptions()
	{
		return [
			static::STATUS_IN_RESERVE => VendorOrder::STATUS_IN_RESERVE,
			static::STATUS_CANCELLED => VendorOrder::STATUS_CANCELLED,
			static::STATUS_COMPLETED => VendorOrder::STATUS_COMPLETED,
		];
	}

	/**
	 * @return array
	 */
	static function getOrderStatusMapperOptions(): array
	{
		return [
			VendorOrder::STATUS_IN_RESERVE => static::STATUS_IN_RESERVE,
			VendorOrder::STATUS_ASSEMBLE => static::STATUS_IN_RESERVE,
			VendorOrder::STATUS_CANCELLED => static::STATUS_CANCELLED,
			VendorOrder::STATUS_COMPLETED => static::STATUS_COMPLETED,
		];
	}

	/**
	 * @param $status
	 * @return string|null
	 */
	static function getVendorOrderStatusByApiStatus($status)
	{
		return ArrayHelper::getValue(static::getOrderStatusMapperOptions(), $status);
	}

	/**
	 * Возвращает наш ID магазина по ID магазина у вендора
	 * @param $id
	 * @return mixed
	 */
	public function getShopIdByVendorShopId($id)
	{
		$preparedId = preg_replace('/^(Vianor_\d+)(_\d+)?$/ui', '$1', $id);
		return ArrayHelper::getValue($this->shopMapper, $preparedId, null);
	}

	/**
	 * @param OrderForm $orderForm
	 * @return \common\models\forms\Order
	 * @throws Exception
	 * @throws Throwable
	 */
	public function createOrder(OrderForm $orderForm)
	{

		/**
		 * @var VendorUser $user
		 */
		$user = Yii::$app->user->getIdentity();

		/**
		 * @var PaymentInterface $payment
		 */
		$payment = $orderForm->getPaymentModel();
		$customer = $orderForm->getCustomer();

		// Действия по созданию заказа

		$order = new \common\models\forms\Order();

		$order->orderId = $this->generateOrderId();
		$order->regionId = $orderForm->getRegion()->getId();

		$order->email = $customer->email;

		$order->counterpartyType = \common\models\forms\Order::COUNTERPARTY_TYPE_ANONIMOUS;
		$order->counterpartyLook = \common\models\forms\Order::COUNTERPARTY_LOOK_FIZIK;
		$order->saleType = \common\models\forms\Order::SALE_TYPE_NALICHNIE;

		$order->reserveForm = true;

		$order->invoiceForm = $payment->getIsInvoiceForm();

		$order->shopId = sprintf('%03s', $orderForm->getShop()->getId());

		$deliveryDate = $orderForm->getDateAsDateTime();
		$reserveDays = DeliveryPickup::RESERV_DAYS + $payment->getReserveExtraDays();

		$order->deliveryDt = (clone $deliveryDate)->format('d.m.Y');

		$order->reserveEndDt = (clone $deliveryDate)
			->modify("+{$reserveDays} day")
			->format('d.m.Y');

		$order->reserveComment = $orderForm->comment;

		$order->phones = $customer->phone;
		$order->smsPhone = $customer->phone;

		$order->counterpartyName = $customer->getFullname();
		$order->counterpartyFullName = $customer->getFullname();

		$order->vendor = $user->getVendor();
		$order->vendorContent = ArrayHelper::getValue($user->orderData, 'vendor-content');
		if ($order->vendorContent == null) {

			$order->vendorContent = $orderForm->id;
		}

		$orderShops = $orderForm->getStoresForOrder();

		$movingRequired = false;
		foreach ($orderShops as $shop) {
			if ($shop['shop_id'] != $order->shopId) {

				$movingRequired = true;
				break;
			}
		}

		if ($order->reserveForm && $movingRequired) {

			//todo Перевести все на массивы
			$order->reserveComment = "ТП;{$order->reserveComment}";
		}

		foreach ($orderShops as $shop)
			$order->addGood(new OrderGoodItem($shop['item_id'], $shop['qty'], $shop['shop_id'], null));

		return $order;
	}

	/**
	 * @param OrderForm $orderForm
	 * @return bool|OrderReserve
	 * @throws \yii\base\Exception
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 * @throws Exception
	 * @throws Throwable
	 */
	public function placeOrder($orderForm)
	{

		if (!$orderForm->validate())
			return false;

		$order = $this->createOrder($orderForm);

		$orderMessage = $order->prepareDataToSend();

		$path = Yii::getAlias('@api/runtime/order');
		if (!is_dir($path)) {
			FileHelper::createDirectory($path);
		}

		$fileNameIn = $path . DIRECTORY_SEPARATOR . $order->vendor . '-' . $order->orderId . '.in';
		$fileNameOut = $path . DIRECTORY_SEPARATOR . $order->vendor . '-' . $order->orderId . '.out';

		file_put_contents($fileNameIn, $orderMessage);

		/**
		 * @var Service1c $service1c
		 */
		$service1c = Yii::$container->get(Service1c::class);

		$orderMessage = base64_encode(mb_convert_encoding($orderMessage, 'cp1251'));

		$orderReserve = $service1c->createOrder($orderMessage);
		$orderForm->addErrors(['product' => $orderReserve->getErrors()]);

		file_put_contents($fileNameOut, $orderReserve->getResponseRaw());

		return $orderReserve->getErrors() === [] ? $orderReserve : false;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function generateOrderId()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0x0fff) | 0x4000, random_int(0, 0x3fff) | 0x8000, random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff));
	}

}
