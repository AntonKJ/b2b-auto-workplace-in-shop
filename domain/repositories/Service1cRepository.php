<?php

namespace domain\repositories;

use common\components\webService\request\CancelOrder;
use common\components\webService\request\CreateOrder;
use common\components\webService\request\GetCreditLimits;
use common\components\webService\request\GetCurrentBalances;
use common\components\webService\request\GetDataForInvoice;
use common\components\webService\request\GetDataOrder;
use common\components\webService\request\GetDebtList;
use common\components\webService\request\GetListOrders;
use common\components\webService\request\GetMutualSettlements;
use common\components\webService\request\GetOrdersBySearchKey;
use common\components\webService\WebService;
use DateTimeImmutable;
use domain\entities\service1c\ClientDebt;
use domain\entities\service1c\ClientDebtCollection;
use domain\entities\service1c\Order;
use domain\entities\service1c\OrderCollection;
use domain\entities\service1c\OrderEntityCollectionInterface;
use domain\entities\service1c\OrderGood;
use domain\entities\service1c\OrderInvoice;
use domain\entities\service1c\OrderReserve;
use domain\entities\service1c\OrderSaleNumber;
use domain\entities\service1c\SearchOrder;
use domain\entities\service1c\SearchOrderCollection;
use domain\entities\service1c\UserCreditLimits;
use domain\entities\service1c\UserMutualSettlements;
use domain\repositories\ar\RepositoryBase;
use Exception;
use InvalidArgumentException;
use ReflectionException;
use Yii;
use yii\base\InvalidConfigException;
use function is_array;
use function is_object;

class Service1cRepository extends RepositoryBase
{

	/**
	 * @var WebService
	 */
	protected $component;
	/**
	 * @var Hydrator
	 */
	protected $hydrator;

	/**
	 * Order1cRepository constructor.
	 * @param Hydrator $hydrator
	 * @throws InvalidConfigException
	 */
	public function __construct(Hydrator $hydrator)
	{
		$this->component = Yii::$app->get('webservice');
		$this->hydrator = $hydrator;
	}

	/**
	 * @param $data
	 * @return array|null
	 * @throws ReflectionException
	 */
	protected function _populateOrderGood($data)
	{
		$goods = [];
		if (is_object($data)) {
			$data = [(array)$data];
		} elseif (is_array($data)) {
			$keys = array_keys($data);
			if (!is_numeric($keys[0])) {
				$data = [$data];
			}
		}
		$unitMapper = array_flip(OrderGood::getUnitOptions());
		foreach ($data as $goodData) {

			if (!is_array($goodData)) {
				$goodData = (array)$goodData;
			}

			$unit = mb_strtolower(trim($goodData['UnitOfMeasurement']));
			$unit = $unitMapper[$unit] ?? $unit;

			$goods[] = $this->hydrator->hydrate(OrderGood::class, [
				'id' => $goodData['Number'],
				'title' => $goodData['Name'],
				'unit' => $unit,
				'amount' => $goodData['Count'],
				'price' => $goodData['Price'],
				'priceTotal' => $goodData['Sum'],
				'priceTotalNDS' => $goodData['SumNDS'] ?? null,
			]);
		}
		return $goods === [] ? null : $goods;
	}

	/**
	 * @param $data
	 * @return array|null
	 * @throws ReflectionException
	 */
	protected function _populateOrderSaleNumber($data): ?array
	{

		$saleNumbers = [];

		if (is_object($data)) {

			$data = [(array)$data];
		} elseif (is_array($data)) {

			$keys = array_keys($data);
			if (!is_numeric($keys[0]))
				$data = [$data];
		}

		foreach ($data as $saleNumber) {

			if (!is_array($saleNumber)) {
				$saleNumber = (array)$saleNumber;
			}

			$saleNumbers[] = $this->hydrator->hydrate(OrderSaleNumber::class, [
				'number' => (string)$saleNumber['Number'],
			]);
		}

		return $saleNumbers === [] ? null : $saleNumbers;
	}

	/**
	 * @param array $data
	 * @return Order|object
	 * @throws Exception
	 */
	protected function _populateOrder(array $data): Order
	{

		$goods = null;
		if (isset($data['Goods'])) {
			$goods = $this->_populateOrderGood($data['Goods']);
		}

		$saleNumbers = null;
		if (isset($data['SalesNumbers'])) {
			$saleNumbers = $this->_populateOrderSaleNumber($data['SalesNumbers']);
		}

		$orderStatus = null;
		if (isset($data['OrderStatus'])) {
			$orderStatusMapper = array_flip(Order::getOrderStatusOptions());
			$orderStatus = $orderStatusMapper[$data['OrderStatus']] ?? $data['OrderStatus'];
		}

		$paymentStatus = $data['PaymentStatus'] ?? null;
		if (isset($data['PaymentStatus'])) {
			$paymentStatusMapper = array_flip(Order::getPaymentStatusOptions());
			$paymentStatus = $paymentStatusMapper[$data['PaymentStatus']] ?? $data['PaymentStatus'];
		}

		$paymentForm = null;
		if (isset($data['PaymentForm'])) {
			$paymentFormMapper = array_flip(Order::getPaymentFormOptions());
			$paymentForm = $paymentFormMapper[$data['PaymentForm']] ?? $data['PaymentForm'];
		}

		$clientDeliveryDate = null;
		if ((bool)$data['Delivery'] &&
			($cDt = new DateTimeImmutable($data['DeliveryDate'])) !== false) {
			$clientDeliveryDate = $cDt;
		}

		return $this->hydrator->hydrate(Order::class, [

			'number' => $data['Number'],
			'priceTotal' => (float)$data['Sum'],

			'orderStatus' => $orderStatus,

			'paymentStatus' => $paymentStatus,
			'paymentForm' => $paymentForm,

			'invoiceNumber' => empty($data['InvoiceNumber']) ? null : $data['InvoiceNumber'],
			'orderDate' => new DateTimeImmutable($data['OrderDate']),
			'endDateReserve' => new DateTimeImmutable($data['EndDateReserve']),

			'store' => empty($data['Store']) ? null : $data['Store'],
			'numberTTN' => empty($data['NumberTTN']) ? null : $data['NumberTTN'],

			'delivery' => (bool)$data['Delivery'],
			'deliveryAddress' => empty($data['DeliveryAddress']) ? null : trim($data['DeliveryAddress']),
			'deliveryDate' => (bool)$data['Delivery'] ? new DateTimeImmutable($data['DeliveryDate']) : null,
			'clientDeliveryDate' => $clientDeliveryDate,

			'commentClient' => empty($data['CommentClient']) ? null : $data['CommentClient'],
			'commentMoving' => empty($data['MovingComment']) ? null : $data['MovingComment'],

			'goods' => $goods,
			'saleNumbers' => $saleNumbers,

			'clientCode' => $data['ClientCode'] ?? null,
			'clientName' => $data['ClientName'] ?? null,
			'phone' => $data['Phone'] ?? null,
		]);
	}

	/**
	 * @param array $data
	 * @return OrderInvoice|object
	 * @throws InvalidArgumentException
	 * @throws ReflectionException
	 */
	protected function _populateInvoice(array $data): OrderInvoice
	{

		$goods = null;
		if (isset($data['Goods']))
			$goods = $this->_populateOrderGood($data['Goods']);

		return $this->hydrator->hydrate(OrderInvoice::class, [

			'organization' => $data['Organization'] ?? null,
			'contractor' => $data['Contractor'] ?? null,
			'buyer' => $data['Buyer'] ?? null,
			'bankBik' => $data['BankBik'] ?? null,
			'bankName' => $data['BankName'] ?? null,
			'settlementAccount' => $data['SettlementAccount'] ?? null,
			'correspondentAccount' => $data['CorrespondentAccount'] ?? null,
			'accountNumber' => $data['AccountNumber'] ?? null,
			'sum' => $data['Sum'] ?? null,
			'sumNDS' => $data['SumNDS'] ?? null,
			'sumInWords' => $data['SumInWords'] ?? null,
			'volume' => $data['Volume'] ?? null,
			'goods' => $goods,

		]);
	}

	/**
	 * @param array $data
	 * @return SearchOrder
	 * @throws Exception
	 */
	protected function _populateSearchOrder(array $data): SearchOrder
	{
		return $this->hydrator->hydrate(SearchOrder::class, [
			'number' => $data['NumberOrder'] ?? null,
			'clientName' => $data['ClientName'] ?? null,
			'clientCode' => $data['ClientCode'] ?? null,
			'comment' => $data['Comment'] ?? null,
			'shop' => $data['Shop'] ?? null,
			'date' => isset($data['Date']) && !empty($data['Date']) ? new DateTimeImmutable($data['Date']) : null,
			'orderType' => isset($data['OrderType']) && !empty($data['OrderType']) ? mb_strtolower($data['OrderType']) : null,
		]);
	}

	/**
	 * @param $clientId
	 * @return OrderEntityCollectionInterface
	 * @throws Exception
	 */
	public function findOrdersByClientId($clientId): OrderEntityCollectionInterface
	{

		$request = new GetListOrders();
		$request->ClientCode = $clientId;

		/**
		 * @var \common\components\webService\response\GetListOrders $response
		 */
		$response = $this->component->send($request);

		$orders = new OrderCollection;
		foreach ($response->getOrders() as $orderData) {

			$orderData = (array)$orderData;

			if (!isset($orderData['Number']) || empty($orderData['Number']))
				continue;

			$orders->add($this->_populateOrder($orderData));
		}

		return $orders;

	}

	/**
	 * @param $clientId
	 * @param $orderNumber
	 * @return Order
	 * @throws NotFoundException
	 * @throws Exception
	 */
	public function findOrderByClientIdAndNumber($clientId, $orderNumber): Order
	{

		$request = new GetDataOrder();

		$request->ClientCode = $clientId;
		$request->OrderNumber = $orderNumber;

		/**
		 * @var \common\components\webService\response\GetDataOrder $response
		 */
		$response = $this->component->send($request);

		$order = (array)$response->getOrderInfo();

		if ($order === []) {
			throw new NotFoundException('Заказ не найден');
		}

		return $this->_populateOrder($order);

	}

	/**
	 * @param $type
	 * @param $query
	 * @param string|null $clientId
	 * @return SearchOrderCollection
	 * @throws Exception
	 */
	public function searchOrdersBy($type, $query, $clientId = null): SearchOrderCollection
	{

		$request = new GetOrdersBySearchKey();

		$request->SearchKey = $type;
		$request->SearchValue = $query;

		if (!empty($clientId))
			$request->ClientCode = $clientId;

		/**
		 * @var \common\components\webService\response\GetOrdersBySearchKey $response ;
		 */
		$response = $this->component->send($request);

		$orders = new SearchOrderCollection();
		foreach ($response->getOrders() as $orderData) {

			$orders->add($this->_populateSearchOrder((array)$orderData));
		}

		return $orders;
	}

	/**
	 * @param string $clientId
	 * @return ClientDebtCollection
	 * @throws ReflectionException
	 */
	public function getClientDebtList(string $clientId): ClientDebtCollection
	{

		$request = new GetDebtList();
		$request->ClientCode = $clientId;

		/**
		 * @var \common\components\webService\response\GetDebtList $response ;
		 */
		$response = $this->component->send($request);

		$items = new ClientDebtCollection();
		foreach ($response->getItems() as $debtItem) {
			$items->add($this->_populateClientDebt((array)$debtItem));
		}
		return $items;
	}

	public function cancelOrder($clientId, $orderNumber)
	{

		$request = new CancelOrder();

		$request->ClientCode = $clientId;
		$request->OrderNumber = $orderNumber;

		/**
		 * @var \common\components\webService\response\CancelOrder $response
		 */
		$response = $this->component->send($request);

		return $response->getResponce();

	}

	/**
	 * @param $clientId
	 * @param $orderNumber
	 * @return OrderInvoice
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function getInvoice($clientId, $orderNumber)
	{
		$request = new GetDataForInvoice();
		$request->OrderNumber = $orderNumber;
		/**
		 * @var \common\components\webService\response\GetDataForInvoice $response
		 */
		$response = $this->component->send($request);
		if (!$response->getStatus()) {
			throw new Order1cResponseException($response->getErrors());
		}
		return $this->_populateInvoice($response->getInvoice());
	}

	/**
	 * @param array $goodIds
	 * @return array
	 * @throws Exception
	 */
	public function getCurrentBalances(array $goodIds)
	{

		$request = new GetCurrentBalances();

		$request->CodeGood = implode(',', $goodIds);

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]));
		/**
		 * @var \common\components\webService\response\GetCurrentBalances $response
		 */
		$response = $this->component->send($request);
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]));

		return $response->getStock();

	}

	/**
	 * @param $order
	 * @return OrderReserve
	 * @throws Exception
	 */
	public function createOrder($order): OrderReserve
	{

		$request = new CreateOrder();

		$request->Order = $order;

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]));

		/**
		 * @var \common\components\webService\response\CreateOrder $response
		 */
		$response = $this->component->send($request);
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]));

		return $this->_populateOrderReserve($response->getResult());
	}

	/**
	 * @param $clientId
	 * @return UserCreditLimits
	 * @throws Exception
	 */
	public function getCreditLimits($clientId): UserCreditLimits
	{

		$request = new GetCreditLimits();

		$request->ClientCode = $clientId;

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]));

		/**
		 * @var \common\components\webService\response\GetCreditLimits $response
		 */
		$response = $this->component->send($request);
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]));

		return $this->_populateCreditLimits($response->getResult());
	}

	/**
	 * @param $clientId
	 * @return UserMutualSettlements
	 * @throws Exception
	 */
	public function getMutualSettlements($clientId): UserMutualSettlements
	{

		$request = new GetMutualSettlements();

		$request->ClientCode = $clientId;

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]));

		/**
		 * @var \common\components\webService\response\GetMutualSettlements $response
		 */
		$response = $this->component->send($request);
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]));

		return $this->_populateMutualSettlements($response->getResult());
	}

	/**
	 * @param array $data
	 * @return OrderReserve
	 * @throws ReflectionException
	 */
	protected function _populateOrderReserve(array $data): OrderReserve
	{

		$invoiceEntity = null;
		if (isset($data['invoice_content_processed']) && is_array($data['invoice_content_processed'])
			&& [] !== $data['invoice_content_processed']) {
			$invoiceEntity = $this->_populateInvoice($data['invoice_content_processed']);
		}

		return $this->hydrator->hydrate(OrderReserve::class, [
			'id' => $data['id'] ?? null,
			'cid' => $data['cid'] ?? null,
			'cid_state' => $data['cid_state'] ?? null,
			'invoice' => $data['invoice'] ?? null,
			'invoice_entity' => $invoiceEntity,
			'invoice_content' => $data['invoice_content'] ?? null,
			'did' => $data['did'] ?? null,
			'shop_state' => $data['shop_state'] ?? null,
			'bik' => $data['bik'] ?? null,
			'bik_state' => $data['bik_state'] ?? null,
			'items' => $data['items'] ?? [],
			'errors' => $data['errors'] ?? [],
			'ccpay_id' => $data['ccpay_id'] ?? null,
			'response_raw' => $data['response_raw'] ?? null,
		]);
	}

	/**
	 * @param array $data
	 * @return UserCreditLimits
	 * @throws ReflectionException
	 */
	protected function _populateCreditLimits(array $data): UserCreditLimits
	{
		return $this->hydrator->hydrate(UserCreditLimits::class, [
			'limit' => $data['limit'] ?? null,
			'currency' => $data['currency'] ?? null,
		]);
	}

	/**
	 * @param array $data
	 * @return UserMutualSettlements
	 * @throws ReflectionException
	 */
	protected function _populateMutualSettlements(array $data): UserMutualSettlements
	{
		return $this->hydrator->hydrate(UserMutualSettlements::class, [
			'balance' => $data['balance'] ?? null,
		]);
	}

	/**
	 * @param array $data
	 * @return ClientDebt
	 * @throws ReflectionException
	 */
	protected function _populateClientDebt(array $data): ClientDebt
	{
		return $this->hydrator->hydrate(ClientDebt::class, [
			'organisation' => $data['Organization'] ?? null,
			'object' => $data['ObjectCalculated'] ?? null,
			'amount' => $data['AmountDebt'] ?? 0,
			'overdueAmount' => $data['AmountОverdueDebt'] ?? 0,
		]);
	}


}
