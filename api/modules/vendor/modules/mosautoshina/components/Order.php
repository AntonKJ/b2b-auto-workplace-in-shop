<?php

namespace api\modules\vendor\modules\mosautoshina\components;

use api\models\VendorOrder;
use api\models\VendorUser;
use api\modules\vendor\modules\mosautoshina\models\forms\OrderForm;
use common\components\deliveries\DeliveryCityRegion;
use common\components\order\OrderGoodItem;
use common\components\payments\PaymentInterface;
use common\models\DeliveryCity;
use common\models\MetroStation;
use common\models\OptUser;
use domain\services\Service1c;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class Order extends Component
{

	const STATUS_ERROR = 'ERROR';
	const STATUS_IN_RESERVE = 'RESERVED';
	const STATUS_COMPLETED = 'COMPLETED';
	const STATUS_CANCELED = 'CANCELED';

	public $addressList;
	public $paymentMethodList;

	protected $_user;

	/**
	 * Список адресов проиндексированных по ID
	 * @return array
	 */
	public function getAddressOptions(): array
	{

		static $_data;
		if ($_data === null) {
			$_data = ArrayHelper::index($this->addressList, 'id');
		}

		return $_data;
	}

	/**
	 * Список адресов проиндексированных по ID
	 * @return array
	 */
	public function getPaymentMethodOptions(): array
	{

		static $_data;
		if ($_data === null) {
			$_data = ArrayHelper::index($this->paymentMethodList, 'code');
		}

		return $_data;
	}

	/**
	 * @return array
	 */
	public static function getOrderStatusOptions(): array
	{
		return [
			static::STATUS_ERROR => VendorOrder::STATUS_CANCELLED,
			static::STATUS_IN_RESERVE => VendorOrder::STATUS_IN_RESERVE,
			static::STATUS_COMPLETED => VendorOrder::STATUS_COMPLETED,
			static::STATUS_CANCELED => VendorOrder::STATUS_CANCELLED,
		];
	}

	/**
	 * @param OrderForm $orderForm
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function createOrder(OrderForm $orderForm)
	{

		/**
		 * @var VendorUser $user
		 */
		$user = \Yii::$app->user->getIdentity();

		/**
		 * @var PaymentInterface $payment
		 */
		$payment = $orderForm->getPaymentModel();
		$b2bUser = $this->getUser();

		// Действия по созданию заказа

		$order = new \common\models\forms\Order();

		$order->orderId = $this->generateOrderId();
		$order->regionId = $b2bUser->region->getId();

		$order->clientCode = $b2bUser->getClientCode();
		$order->email = $b2bUser->getEmail();

		$order->counterpartyType = \common\models\forms\Order::COUNTERPARTY_TYPE_CLIENT;
		$order->counterpartyLook = \common\models\forms\Order::COUNTERPARTY_LOOK_URIK;


		// Тип продажи на основе способа оплаты
		$order->saleType = $payment->getIdNumber();

		// Формировать резерв
		$order->reserveForm = true;

		// Формировать доставку
		$order->deliveryForm = true;

		// Формировать счёт на основе способа оплаты
		$order->invoiceForm = $payment->getIsInvoiceForm();

		// Склад на основе типа заказа
		$order->shopId = sprintf('%03s', $orderForm->getShop());

		// Адрес доставки
		$order->deliveryAddress = $orderForm->getAddressModel()->getAddress();

		// Дни доставки
		$order->deliveryDays = $b2bUser->region->getDeliveryDaysMask();

		// Пункт назначения
		$order->destination = $b2bUser->region->getTitle();

		$geoPosition = $orderForm->getAddressModel()->getGeoPosition();

		$poi = $orderForm->getClosestPoi();
		switch (true) {

			case ($poi instanceOf MetroStation):

				$order->destination .= "; м. {$poi->getTitle()}";

				// Дополняем адрес ближайшей станцией метро
				$order->deliveryAddress = implode('; ', [
					$order->destination,
					$orderForm->getAddressModel()->getAddress(),
				]);

				break;

			case ($poi instanceOf DeliveryCity):

				// Заново устанавливаем пункт назначения
				$order->destination = $poi->getTitle();

				// И обновляем адрес
				$order->deliveryAddress = implode('; ', [
					"Доставка в {$order->destination}",
					$orderForm->getAddressModel()->getAddress(),
				]);

				// Адрес который ввёл клиент
				$order->actualAddress = $orderForm->getAddressModel()->getAddress();
				break;

		}

		$order->deliveryAddress .= "; [GPS {$geoPosition->__toString()}]";

		/**
		 * @var \DateTime $deliveryDate
		 */
		$deliveryDate = $orderForm->getDateAsDateTime();

		$reserveDays = 0;
		if ($payment instanceOf PaymentInvoice) { 
			$reserveDays = 3 + $payment->getReserveExtraDays();
		}

		// Дата доставки
		$order->deliveryDt = (clone $deliveryDate)->format('d.m.Y');

		// Дата клиента
		$order->reserveEndDt = (clone $deliveryDate)
			->modify("+{$reserveDays} day")
			->format('d.m.Y');

		// Комментарий, если есть
		if (!empty($orderForm->comment))
			$order->deliveryComment = "{$orderForm->comment}; " . $order->deliveryComment;

		$order->deliveryComment = trim($order->deliveryComment);

		$order->vendor = $user->getVendor();
		$order->vendorContent = ArrayHelper::getValue($user->orderData, 'vendor-content');

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
	 * @return bool|\domain\entities\service1c\OrderReserve
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\di\NotInstantiableException
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function placeOrder($orderForm)
	{

		if (!$orderForm->validate())
			return false;

		$order = $this->createOrder($orderForm);

		$orderMessage = $order->prepareDataToSend();

		$path = \Yii::getAlias('@api/runtime/order');
		if (!is_dir($path))
			FileHelper::createDirectory($path);

		$fileNameIn = $path . DIRECTORY_SEPARATOR . $order->vendor . '-' . $order->orderId . '.in';
		$fileNameOut = $path . DIRECTORY_SEPARATOR . $order->vendor . '-' . $order->orderId . '.out';

		file_put_contents($fileNameIn, $orderMessage);

		/**
		 * @var Service1c $service1c
		 */
		$service1c = \Yii::$container->get(Service1c::class);

		$orderMessage = base64_encode(mb_convert_encoding($orderMessage, 'cp1251'));

		$orderReserve = $service1c->createOrder($orderMessage);
		$orderForm->addErrors(['product' => $orderReserve->getErrors()]);

		file_put_contents($fileNameOut, $orderReserve->getResponseRaw());

		return $orderReserve->getErrors() === [] ? $orderReserve : false;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function generateOrderId()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0x0fff) | 0x4000, random_int(0, 0x3fff) | 0x8000, random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff));
	}

	/**
	 * @return OptUser
	 */
	public function getUser(): OptUser
	{
		return $this->_user;
	}

	/**
	 * @param OptUser $user
	 * @return Order
	 */
	public function setUser(OptUser $user): self
	{
		$this->_user = $user;
		return $this;
	}


}
