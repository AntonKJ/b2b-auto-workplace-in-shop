<?php

namespace common\components\deliveries;

use common\components\deliveries\forms\DeliveryRussiaTcForm;
use common\components\payments\PaymentInvoice;
use common\models\DeliveryTcCity;
use common\models\OrderType;
use common\models\OrderTypeGroup;
use common\models\Shop;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use function is_array;

class DeliveryRussiaTc extends DeliveryAbstract
{

	// Колво дней на резерв
	public const RESERVE_DAYS = 9;

	protected $_activeOrderTypes;

	/**
	 * @return array
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 */
	public function getActiveOrderTypes(): array
	{
		if (!is_array($this->_activeOrderTypes))
			$this->getData();

		return $this->_activeOrderTypes;
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	public function getData()
	{

		static $data;

		Yii::beginProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');
		if (null === $data) {

			$user = $this->getUser();
			$region = $this->getRegion();

			/**
			 * @var int[] $orderTypeGroup
			 */
			$orderTypeGroup = [$region->getOrderTypeGroupId()];

			if ($user !== null && $user->category !== null)
				$orderTypeGroup[] = $user->getOrderTypeGroupId();

			$otIds = OrderTypeGroup::calculateOrderTypeGroupIntersect($orderTypeGroup);

			$query = OrderType::find()
				->byCategory(static::getCategory())
				->defaultOrder()
				->byOrderTypeGroup($region)
				->byId($otIds);

			$data = [];

			$orderTypeByShops = [];
			foreach ($query->each() as $ot) {
				$orderTypeByShops[(int)$ot->from_shop_id][(int)$ot->getId()] = $ot;
			}

			// Берем ID активных магазинов для региона
			$regionShopIds = Shop::find()
				->select(['shop_id'])
				->active()
				->column();

			$regionShopIds = array_fill_keys($regionShopIds, null);

			$orderTypeByShops = array_intersect_key($orderTypeByShops, $regionShopIds);

			$orderTypesCollection = [];
			$shopsByOrderType = [];

			foreach ($orderTypeByShops as $shopId => $ots)
				foreach ($ots as $otId => $ot) {

					$shopsByOrderType[$otId][] = $shopId;

					if (!isset($orderTypesCollection[$otId]))
						$orderTypesCollection[$otId] = $ot;
				}

			$active = false;

			$this->_activeOrderTypes = [];

			foreach ($shopsByOrderType as $otId => $shopsIds) {

				// Получаем список магазинов с наличием и днями когда можно забрать товар
				$shopsWithGoods = $this->getDeliveryComponent()
					->getPickupStoresForGoods($this->goods->getData(), $region->getZoneId(), $otId);

				// Фильтруем магазины, где есть товар
				$shopsWithGoods = array_intersect_key($shopsWithGoods, array_fill_keys($shopsIds, null));

				// Если нет магазинов с товаром, переходим дальше
				if ([] === $shopsWithGoods) {
					continue;
				}

				$active = true;

				$this->_activeOrderTypes[] = $orderTypesCollection[$otId];

				break;
			}

			$cities = [];
			$deliveryTc = [];

			if ($active) {

				$cities = DeliveryTcCity::find()
					->defaultOrder()
					->all();

				foreach ($this->getDeliveryComponent()->getDeliveryTcOptions() as $key => $title) {
					$deliveryTc[] = [
						'id' => $key,
						'title' => $title,
					];
				}
			}

			if ($active === false) {
				Yii::info("{$this->getTitle()} недоступна, т.к. нет доступных магазинов с товаром.");
				if (isset($shopsByOrderType)) {
					Yii::info($shopsByOrderType);
				}
			}

			$payments = $this->getPayments();
			if ($payments === []) {
				Yii::info("{$this->getTitle()} недоступна, т.к. нет доступных способов оплаты для пользователя.");
				$active = false;
			}

			$data = [
				'active' => $active,
				'items' => $cities,
				'tc' => $deliveryTc,
				'payments' => $payments,
			];
		}
		Yii::endProfile(implode('_', [__CLASS__, __METHOD__]), 'delivery');

		return $data;
	}

	/**
	 * @return bool
	 * @throws Throwable
	 */
	public function isActive(): bool
	{
		$data = $this->getData();
		return isset($data['active']) && $data['active'];
	}

	public function getTitle(): string
	{
		return 'Доставка транспортной компанией';
	}

	static public function getCategory(): string
	{
		return OrderType::CATEGORY_RUSSIA_TC;
	}

	/**
	 * @return array|null
	 * @throws Throwable
	 */
	public function getPayments()
	{

		static $types;

		if ($types === null) {

			$types = [
				PaymentInvoice::getCode() => new PaymentInvoice(),
			];

			if ($this->getUser() !== null && $this->getUser()->category !== null) {

				$allowedTypes = $this->getUser()->getPaymentTypes();
				$allowedTypes = array_flip($allowedTypes);

				$types = array_intersect_key($types, $allowedTypes);
				$types = array_values($types);
			}
		}

		return $types;
	}

	/**
	 * @return DeliveryRussiaTcForm
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 */
	public function getFormModel()
	{
		$data = $this->isActive() ? $this->getData() : [];
		return new DeliveryRussiaTcForm($data['items'] ?? [], $data['tc'] ?? [], $data['payments'] ?? [], $this->getActiveOrderTypes());
	}
}
