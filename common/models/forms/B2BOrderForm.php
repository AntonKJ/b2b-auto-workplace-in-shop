<?php

namespace common\models\forms;

use common\components\deliveries\DeliveryCityRegion;
use common\components\deliveries\DeliveryInterface;
use common\components\deliveries\DeliveryPickup;
use common\components\deliveries\DeliveryRussia;
use common\components\deliveries\DeliveryRussiaTc;
use common\components\deliveries\forms\DeliveryCityRegionForm;
use common\components\deliveries\forms\DeliveryFormInterface;
use common\components\deliveries\forms\DeliveryPickupForm;
use common\components\deliveries\forms\DeliveryRussiaForm;
use common\components\deliveries\forms\DeliveryRussiaTcForm;
use common\components\Delivery;
use common\components\deliverySchedule\DeliveryScheduleInterface;
use common\components\order\OrderGoodItem;
use common\components\payments\PaymentInterface;
use common\components\payments\PaymentInvoice;
use common\components\ShoppingCartItem;
use common\models\DeliveryCity;
use common\models\MetroStation;
use common\models\OptUser;
use common\models\OptUserAddress;
use common\models\OptUserCategory;
use common\models\OrderType;
use common\models\Region;
use common\models\Zone;
use DateTime;
use domain\entities\service1c\OrderReserve;
use domain\services\GoodAvailabilityService;
use domain\services\Service1c;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;
use yii\di\NotInstantiableException;
use yii\helpers\FileHelper;
use yii\web\IdentityInterface;
use function count;
use function is_array;

/**
 * @property DeliveryFormInterface|DeliveryPickupForm|DeliveryCityRegionForm|DeliveryRussiaForm|DeliveryRussiaTcForm $deliveryModel
 */
class B2BOrderForm extends Model
{

	public $deliveryType;
	public $delivery;
	public $goods;

	/**
	 * @var null|Order
	 */
	protected $_order;

	protected $_orderComponent;
	protected $_deliveryModel;

	/**
	 * @var Region
	 */
	protected $_region;

	/**
	 * @var Delivery
	 */
	protected $_deliveryComponent;

	public function __construct(\common\components\order\Order $order, array $config = [])
	{
		$this->_orderComponent = $order;
		parent::__construct($config);
	}

	/**
	 * @return Region
	 */
	public function getRegion()
	{
		if ($this->_region === null) {
			$this->_region = Yii::$app->region->current;
		}
		return $this->_region;
	}

	/**
	 * @return Delivery
	 */
	public function getDeliveryComponent()
	{
		if ($this->_deliveryComponent === null) {
			$this->_deliveryComponent = Yii::$app->delivery;
		}
		return $this->_deliveryComponent;
	}

	/**
	 * @return DeliveryInterface
	 */
	public function getDeliveryTypeComponent()
	{
		return $this->_orderComponent->getDeliveryByType($this->deliveryType);
	}

	public function attributeLabels()
	{
		return [
			'deliveryType' => 'Способ получения заказа',
			'delivery' => 'Информация о доставке',
		];
	}

	/**
	 * @return DeliveryFormInterface
	 */
	protected function _fetchDeliveryModel()
	{
		return $this->getDeliveryTypeComponent()->getFormModel();
	}

	/**
	 * @return DeliveryFormInterface|DeliveryPickupForm|DeliveryCityRegionForm|DeliveryRussiaForm|DeliveryRussiaTcForm
	 */
	public function getDeliveryModel()
	{
		if ($this->_deliveryModel === null) {
			$this->_deliveryModel = $this->_fetchDeliveryModel();
			if ($this->_deliveryModel instanceof DeliveryPickupForm) {
				$this->_deliveryModel->setCommentNotEmpty($this->_orderComponent->isNeedValidateComment());
			}
		}
		return $this->_deliveryModel;
	}

	/**
	 * @return array
	 */
	public function getDeliveryTypeOptions()
	{
		static $options = null;
		if (null === $options) {
			$options = $this->_orderComponent->getActiveDeliveries();
		}
		return $options;
	}

	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['deliveryType'], 'required', 'message' => 'Выберите способ получения заказа'],
			[['deliveryType'], 'in', 'range' => array_keys($this->getDeliveryTypeOptions()),
				'message' => 'Неизвестный способ получения заказа'],
			[['delivery'], 'required'],
			[['delivery'], 'validateDelivery', 'skipOnEmpty' => false],
			['goods', 'validateGoods', 'skipOnEmpty' => false, 'skipOnError' => true],
		];
	}

	/**
	 * @return array
	 */
	public function getGoods()
	{
		return $this->_orderComponent->getGoods();
	}

	/**
	 * @param array $goodIds
	 * @param int $cacheDuration на сколько секунд кешировать данные
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 */
	protected function updateStockBalances(array $goodIds, int $cacheDuration = 15)
	{
		if ($goodIds === []) {
			return;
		}
		$goodIdsKey = array_keys($goodIds);
		// Сортируем массив, для нормализации ключа
		if (count($goodIdsKey) > 1) {
			sort($goodIdsKey);
		}
		$key = $this->getCache()->buildKey($goodIdsKey);
		if (($stocks = $this->getCache()->get($key)) === false) {
			// Получаем остатки для товаров в корзине
			$stocks = Yii::$container->get(Service1c::class)
				->getCurrentBalances(array_keys($goodIds));
			if (is_array($stocks)) {
				/** @var GoodAvailabilityService $availability */
				$availability = $this->getDeliveryComponent()->getAvailabilityService();
				$zoneIds = Zone::getZoneIds();
				// Обновляем остатки
				foreach ($goodIds as $goodId => $type) {
					$stock = $stocks[$goodId] ?? [];
					$stock = array_merge($availability->getAvailablePreorderFromCache($goodId), $stock);
					$availability->updateCache($goodId, $zoneIds, $stock);
				}
			}
			$this->getCache()->set($key, $stocks, $cacheDuration);
		}
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 * @throws Throwable
	 */
	public function validateGoods($attribute, $params, $validator)
	{
		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}
		$goodIds = [];
		/**
		 * @var ShoppingCartItem $good
		 */
		foreach ($this->_orderComponent->getGoods() as $good) {
			$goodIds[$good->getGood()->getId()] = $good->getItem()->getEntityType();
		}
		// Обновляем текущее наличие
		$this->updateStockBalances($goodIds);
		// Получаем список магазинов/складов для формирования заказа
		$orderShops = $this->getStoresForItems();
		if ($orderShops === []) {
			$this->addError($attribute, 'Доступны не все заказанные позиции. Наличие товара изменилось.');
		}
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	protected function getStoresForItems()
	{
		static $data;
		if (null === $data) {
			$data = $this->getDeliveryComponent()
				->getStoresForShoppingCartItems(
					$this->getGoods(),
					$this->getRegion(),
					$this->deliveryModel->getOrderType()->getId(),
					$this->deliveryModel->getShopId()
				);
		}
		return $data;
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 */
	public function validateDelivery($attribute, $params, $validator)
	{
		$this->deliveryModel->load($this->{$attribute}, '');
		if (!$this->deliveryModel->validate()) {
			foreach ($this->deliveryModel->getErrors() as $field => $messages) {
				$this->addError("{$this->deliveryType}_{$field}", $messages);
			}
		}
	}

	/**
	 * @return IdentityInterface|null
	 * @throws Throwable
	 */
	public function getUser()
	{
		return Yii::$app->user->getIdentity();
	}

	/**
	 * @param int $shopId
	 * @return int
	 */
	protected function getShopIdFor1C($shopId)
	{
		$mapper = [
			30000 => 680,
			30001 => 680,
			30002 => 680,
			30003 => 698,
			30703 => 703,
		];
		return $mapper[$shopId] ?? $shopId;
	}

	/**
	 * @return Order
	 * @throws Exception
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	protected function createOrder()
	{

		/**
		 * @var Region $region
		 */
		$region = $this->getRegion();

		/**
		 * @var Delivery $deliveryComponent
		 */
		$deliveryComponent = Yii::$app->delivery;

		/**
		 * @var PaymentInterface $paymentModel
		 */
		$paymentModel = $this->deliveryModel->getPaymentModel();

		/**
		 * @var DeliveryScheduleInterface|Model|null $scheduleModel
		 */
		$scheduleModel = $this->deliveryModel->getScheduleModel();

		/**
		 * @var OptUser $user
		 */
		$user = $this->getUser();

		// Действия по созданию заказа

		$order = new Order();

		// Номер заказа
		$order->orderId = $this->generateOrderId();

		// ID региона
		$order->regionId = $region->getId();

		// Код постоянного пользователя (покупателя)
		$order->clientCode = $user->getClientCode();

		// Емайл адрес
		$order->email = $user->getEmail();

		// Тип контрагента
		$order->counterpartyType = Order::COUNTERPARTY_TYPE_CLIENT;

		// Вид контрагента
		$order->counterpartyLook = Order::COUNTERPARTY_LOOK_URIK;

		switch ($this->deliveryType) {

			case DeliveryPickup::getCategory():
			{
				// самовывоз

				// Типр продажи
				$order->saleType = $paymentModel->getIdNumber();

				// Формировать резерв
				$order->reserveForm = true;

				// Выбранный магазин
				$order->shopId = sprintf('%03s', $this->getShopIdFor1C($this->deliveryModel->getShopId()));

				// Выбранная дата
				$deliveryDate = $this->deliveryModel->getDateAsDateTime();

				// Дней на резерв = + дополнительные дни на основе типа оплаты
				$reserveDays = DeliveryPickup::RESERV_DAYS + $paymentModel->getReserveExtraDays();

				// Дата самовывоза
				$order->deliveryDt = (clone $deliveryDate)->format('d.m.Y');

				// Дата окончания резерва
				$order->reserveEndDt = (clone $deliveryDate)
					->modify("+{$reserveDays} day")
					->format('d.m.Y');

				// Формировать счёт на основе выбранного способа оплаты
				$order->invoiceForm = $paymentModel->getIsInvoiceForm();

				// Если оплата за нал, только для пользователей с определённой категорией
				if (!in_array($user->getCategoryId(), [OptUserCategory::CATEGORY_REGION, OptUserCategory::CATEGORY_REGION_TC])
					&& !($paymentModel instanceof PaymentInvoice)) {
					$order->invoiceForm = false;
					$order->saleType = Order::SALE_TYPE_NALICHNIE;
				}

				// Комментарий, если есть
				if (!empty($this->deliveryModel->comment)) {
					$order->clientComment = "{$this->deliveryModel->comment}; " . $order->clientComment;
				}

				// Время доставки, если есть
				if ($scheduleModel instanceof DeliveryScheduleInterface) {
					$order->deliveryInterval = $scheduleModel->getValue();
					$order->reserveComment = "{$scheduleModel->getTitle()}; " . $order->reserveComment;
				}

				$order->reserveComment = trim($order->reserveComment);
				$order->clientComment = trim($order->clientComment);

				break;
			}

			case DeliveryCityRegion::getCategory():
			{

				// Тип продажи на основе способа оплаты
				$order->saleType = $paymentModel->getIdNumber();

				// Формировать резерв
				$order->reserveForm = true;

				// Формировать доставку
				$order->deliveryForm = true;

				// Формировать счёт на основе способа оплаты
				$order->invoiceForm = $paymentModel->getIsInvoiceForm();

				/**
				 * @var OrderType $orderType
				 */
				$orderType = $this->deliveryModel->getOrderType();

				// Склад на основе типа заказа
				$order->shopId = sprintf('%03s', $this->getShopIdFor1C($orderType->from_shop_id));

				// Адрес доставки
				$order->deliveryAddress = $this->deliveryModel->address;

				// Дни доставки
				$order->deliveryDays = $region->getDeliveryDaysMask();

				// Пункт назначения
				$order->destination = $region->getTitle();

				$geoPosition = $this->deliveryModel->getGeoPosition();

				$poi = $this->deliveryModel->getClosestPoi();
				switch (true) {

					case ($poi instanceOf MetroStation):

						$order->destination .= "; м. {$poi->getTitle()}";

						// Дополняем адрес ближайшей станцией метро
						$order->deliveryAddress = implode('; ', [
							$order->destination,
							$this->deliveryModel->address,
						]);

						break;

					case ($poi instanceOf DeliveryCity):

						// Заново устанавливаем пункт назначения
						$order->destination = $poi->getTitle();

						// И обновляем адрес
						$order->deliveryAddress = implode('; ', [
							"Доставка в {$order->destination}",
							$this->deliveryModel->address,
						]);

						// Адрес который ввёл клиент
						$order->actualAddress = $this->deliveryModel->address;
						break;

				}

				if (!empty($this->deliveryModel->autoAddress)) {
					$order->deliveryAddress .= sprintf('; [GPS %s; %s]', (string)$geoPosition, $this->deliveryModel->autoAddress);
				}

				/**
				 * @var DateTime $deliveryDate
				 */
				$deliveryDate = $this->deliveryModel->getDateAsDateTime();
				$reserveDays = DeliveryCityRegion::MAX_RESERVE_DAYS;

				$today = new DateTime();
				$period = $today->diff($deliveryDate);

				if ($period->days < $paymentModel->getReserveExtraDays()) {
					$reserveDays += $paymentModel->getReserveExtraDays() - $period->days;
				}

				// Дата доставки
				$order->deliveryDt = (clone $deliveryDate)->format('d.m.Y');

				// Дата клиента
				$order->reserveEndDt = (clone $deliveryDate)
					->modify("+{$reserveDays} day")
					->format('d.m.Y');

				// Комментарий, если есть
				if (!empty($this->deliveryModel->comment)) {
					$order->deliveryComment = "{$this->deliveryModel->comment}; " . $order->deliveryComment;
				}

				// Время доставки, если есть
				if ($scheduleModel instanceof DeliveryScheduleInterface) {
					$order->deliveryInterval = $scheduleModel->getValue();
					$order->deliveryComment = "{$scheduleModel->getTitle()}; " . $order->deliveryComment;
				}

				$order->deliveryComment = trim($order->deliveryComment);
				break;
			}

			case DeliveryRussia::getCategory():
			{
				// доставка машиной по России

				$orderType = $this->getDeliveryModel()->getOrderType();

				// Адрес доставки
				$order->deliveryAddress = $this->deliveryModel->getFullAddress();

				/**
				 * @var DateTime $date
				 */
				$date = $this->deliveryModel->getDateAsDateTime();

				// Дата доставки
				$order->deliveryDt = $date->format('d.m.Y');

				// Дата окончания резерва + дополнительные дни за безнал
				$reservDays = DeliveryRussia::MAX_RESERVE_DAYS;

				$today = new DateTime();
				$period = $today->diff($date);

				if ($period->days < $paymentModel->getReserveExtraDays())
					$reservDays += $paymentModel->getReserveExtraDays() - $period->days;

				$order->reserveEndDt = (clone $date)
					->modify("+{$reservDays} day")
					->format('d.m.Y');

				// Номер скалада из типа заказа
				$order->shopId = sprintf('%03s', $this->getShopIdFor1C($orderType->from_shop_id));

				// Пункт назначения
				$order->destination = $this->deliveryModel->getCityText();

				// Тип продажи
				$order->saleType = $paymentModel->getIdNumber();

				// Формировать счёт на основе формы оплаты
				$order->invoiceForm = $paymentModel->getIsInvoiceForm();

				// формировать резерв
				$order->reserveForm = true;

				// формировать доставку
				$order->deliveryForm = true;

				// комментарий доставки
				$order->deliveryComment = $this->deliveryModel->getFullAddress();

				// комментарий доставки
				$order->deliveryRussia = true;

				if (!empty($this->deliveryModel->comment)) {
					$order->deliveryComment = "{$this->deliveryModel->comment}; " . $order->deliveryComment;
				}

				// Время доставки, если есть
				if ($scheduleModel instanceof DeliveryScheduleInterface) {
					$order->deliveryInterval = $scheduleModel->getValue();
					$order->deliveryComment = "{$scheduleModel->getTitle()}; " . $order->deliveryComment;
				}

				$order->deliveryComment = trim($order->deliveryComment);
				break;
			}

			case DeliveryRussiaTc::getCategory():
			{
				// доставка транспортной компанией

				$orderType = $this->getDeliveryModel()->getOrderType();

				// С какого склада доставка
				$order->shopId = sprintf('%03s', $this->getShopIdFor1C($orderType->from_shop_id));

				/**
				 * @var DateTime $date
				 */
				$date = new DateTime();

				// Дата доставки сегодня
				$order->deliveryDt = $date->format('d.m.Y');

				$reservDays = DeliveryRussiaTc::RESERVE_DAYS;

				// Дата окончания резерва
				$order->reserveEndDt = (clone $date)
					->modify("+{$reservDays} day")
					->format('d.m.Y');

				// Город доставки
				$order->deliveryAddress = $this->deliveryModel->getCityText();

				// Тип продажи на основе способа оплаты (безнал)
				$order->saleType = $paymentModel->getIdNumber();

				// Формировать счёт на основе типа оплаты (безнал)
				$order->invoiceForm = $paymentModel->getIsInvoiceForm();

				// Формировать резерв
				$order->reserveForm = true;

				// Формировать доставку
				$order->deliveryForm = true;

				// Какой транспортной компанией доставлять
				$order->deliveryTc = $this->deliveryModel->getTcText();

				// Добавляем комментарий по ТК
				$order->deliveryComment = "ТК: {$order->deliveryTc}";

				// Доставка по России
				$order->deliveryRussia = true;

				if (!empty($this->deliveryModel->comment)) {
					$order->deliveryComment = "{$this->deliveryModel->comment}; " . $order->deliveryComment;
				}

				// Время доставки, если есть
				if ($scheduleModel instanceof DeliveryScheduleInterface) {
					$order->deliveryInterval = $scheduleModel->getValue();
					$order->deliveryComment = "{$scheduleModel->getTitle()}; " . $order->deliveryComment;
				}

				$order->deliveryComment = trim($order->deliveryComment);

				break;
			}

		}

		$orderShops = $this->getStoresForItems();

		$order->setMovingRequired(false);
		foreach ($orderShops as $shop) {
			if ($shop['shop_id'] != $order->shopId) {
				$order->setMovingRequired(true);
				break;
			}
		}

		if ($order->reserveForm && $order->isMovingRequired()) {
			$order->reserveComment = trim('ТП; ' . $order->reserveComment);
		}

		$goods = $this->_orderComponent->getGoods();

		foreach ($orderShops as $shop) {
			$order->addGood(new OrderGoodItem($shop['item_id'], $shop['qty'], $shop['shop_id'], isset($goods[$shop['item_id']]) ? $goods[$shop['item_id']]->getGood()->getPrice() : null));
			if ((int)$shop['shop_id'] > 10000) {
				$order->invoiceForm = false;
			}
		}

		return $order;
	}

	/**
	 * @return Order|null
	 */
	public function getOrder(): Order
	{
		return $this->_order;
	}

	/**
	 * @return bool|OrderReserve
	 * @throws Exception
	 * @throws \yii\base\Exception
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 * @throws Throwable
	 */
	public function placeOrder()
	{

		if (!$this->validate()) {
			return false;
		}

		$this->_order = $this->createOrder();

		$orderMessage = $this->_order->prepareDataToSend();

		$path = Yii::getAlias('@b2b/runtime/order');
		if (!is_dir($path)) {
			FileHelper::createDirectory($path);
		}

		$fileNameIn = $path . DIRECTORY_SEPARATOR . $this->_order->orderId . '.in';
		$fileNameOut = $path . DIRECTORY_SEPARATOR . $this->_order->orderId . '.out';

		file_put_contents($fileNameIn, $orderMessage);

		/**
		 * @var Service1c $service1c
		 */
		$service1c = Yii::$container->get(Service1c::class);

		//$orderMessage = base64_encode(mb_convert_encoding($orderMessage, 'cp1251'));
		$orderMessage = base64_encode($orderMessage);

		$orderReserve = $service1c->createOrder($orderMessage);
		$this->addErrors(['goods' => $orderReserve->getErrors()]);

		Yii::debug($orderReserve, 'application.order');
		file_put_contents($fileNameOut, $orderReserve->getResponseRaw());

		//todo вынести в стратегии
		// сохраняем адрес
		$deliveryModel = $this->getDeliveryModel();
		if ($deliveryModel->isAllowedAddressStore() && null !== $this->getUser()) {

			$address = new OptUserAddress();

			$address->opt_user_id = $this->getUser()->getId();

			$deliveryModel->loadAddressAttributes($address);

			$address->hash = $address->generateHash();

			$existAddress = OptUserAddress::find()
				->byOptUserId($address->opt_user_id)
				->byDeliveryType($address->type)
				->byHash($address->hash)
				->one();

			if ($existAddress === null) {
				$address->save();
			} else {
				$existAddress->touchUpdatedAt();
			}
		}

		// Чистим кеш по текущему пользователю
		TagDependency::invalidate($this->getCache(), [$this->getCacheTagsUserOrder()]);

		return $orderReserve->getErrors() === [] ? $orderReserve : false;
	}

	/**
	 * @return CacheInterface
	 */
	protected function getCache()
	{
		return Yii::$app->cache;
	}

	protected function getCacheTagsUserOrder()
	{
		$userId = Yii::$app->user->getId();
		return "orders-user-{$userId}";
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
