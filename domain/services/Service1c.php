<?php

namespace domain\services;

use domain\entities\service1c\ClientDebtCollection;
use domain\entities\service1c\Order;
use domain\entities\service1c\OrderEntityCollectionInterface;
use domain\entities\service1c\OrderInvoice;
use domain\entities\service1c\OrderReserve;
use domain\entities\service1c\SearchOrderCollection;
use domain\entities\service1c\UserCreditLimits;
use domain\entities\service1c\UserMutualSettlements;
use domain\repositories\Service1cRepository;
use Exception;
use Yii;

class Service1c
{

	protected $repository;

	/**
	 * Order1cService constructor.
	 * @param Service1cRepository $repository
	 */
	public function __construct(Service1cRepository $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * @param $clientId
	 * @return OrderEntityCollectionInterface
	 * @throws Exception
	 */
	public function getOrderList($clientId): OrderEntityCollectionInterface
	{
		return $this->repository->findOrdersByClientId($clientId);
	}

	/**
	 * @param $clientId
	 * @return UserCreditLimits
	 * @throws Exception
	 */
	public function getCreditLimits($clientId): UserCreditLimits
	{
		return $this->repository->getCreditLimits($clientId);
	}

	/**
	 * @param $clientId
	 * @return UserMutualSettlements
	 * @throws Exception
	 */
	public function getMutualSettlements($clientId): UserMutualSettlements
	{
		return $this->repository->getMutualSettlements($clientId);
	}

	/**
	 * @param $clientId
	 * @return UserCreditLimits
	 * @throws Exception
	 */
	public function getBalance($clientId): UserCreditLimits
	{
		return $this->repository->getCreditLimits($clientId);
	}

	/**
	 * @param string $clientId
	 * @return ClientDebtCollection
	 * @throws Exception
	 */
	public function getClientDebt(string $clientId): ClientDebtCollection
	{
		return $this->repository->getClientDebtList($clientId);
	}

	/**
	 * @param integer $type
	 * @param string $query
	 * @param null|string $clientId
	 * @return SearchOrderCollection
	 * @throws Exception
	 */
	public function searchOrdersBy($type, $query, $clientId = null): SearchOrderCollection
	{
		return $this->repository->searchOrdersBy($type, $query, $clientId);
	}

	/**
	 * @param $clientId
	 * @param $orderNumber
	 * @return Order
	 * @throws Exception
	 */
	public function getOrderInfo($clientId, $orderNumber): Order
	{
		return $this->repository->findOrderByClientIdAndNumber($clientId, $orderNumber);
	}

	/**
	 * @param $clientId
	 * @param $orderNumber
	 * @return array
	 */
	public function cancelOrder($clientId, $orderNumber)
	{
		return $this->repository->cancelOrder($clientId, $orderNumber);
	}

	/**
	 * @param $clientId
	 * @param $orderNumber
	 * @return OrderInvoice
	 */
	public function getInvoice($clientId, $orderNumber)
	{
		return $this->repository->getInvoice($clientId, $orderNumber);
	}

	/**
	 * @param array $goodIds
	 * @return array
	 */
	public function getCurrentBalances(array $goodIds)
	{
		$stocks = $this->repository->getCurrentBalances($goodIds);
		Yii::info($stocks);

		return $stocks;
	}

	/**
	 * @param $order
	 * @return OrderReserve
	 */
	public function createOrder($order)
	{
		return $this->repository->createOrder($order);
	}

}
