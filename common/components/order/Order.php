<?php

namespace common\components\order;

use common\components\deliveries\DeliveryGood;
use common\components\deliveries\DeliveryGoodCollection;
use common\components\deliveries\DeliveryInterface;
use common\components\deliveries\DeliveryPickup;
use common\components\ShoppingCartItem;
use common\interfaces\B2BUserInterface;
use common\interfaces\RegionEntityInterface;
use common\models\Autopart;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use function in_array;
use function is_array;

class Order extends Component
{

	protected $region;
	protected $user;

	protected $goods;
	protected $deliveries = [];

	public $date;

	public function __construct(RegionEntityInterface $region, B2BUserInterface $user, array $goods, array $deliveries, array $config = [])
	{

		$this->region = $region;
		$this->user = $user;

		$this->goods = $goods;

		$deliveryGoods = new DeliveryGoodCollection();

		/** @var ShoppingCartItem $good */
		foreach ($this->goods as $good) {
			$deliveryGoods->addGood($good->getItem()->getEntityId(), $good->getItem()->quantity);
		}

		foreach ($deliveries as $delivery) {

			/**
			 * @var DeliveryInterface $delivery
			 */
			$delivery = new $delivery($region, $user, $deliveryGoods);
			$this->deliveries[$delivery::getCategory()] = $delivery;
		}

		parent::__construct($config);
	}

	/**
	 * @return array
	 */
	public function getGoods()
	{
		return $this->goods;
	}

	/**
	 * @return bool
	 */
	public function getIsPreordered()
	{
		$isPreordered = false;
		/**
		 * @var ShoppingCartItem $good
		 */
		foreach ($this->goods as $good) {
			$isPreordered = $isPreordered || $good->getIsPreordered();
		}
		return $isPreordered;
	}

	/**
	 * @param bool $refresh
	 * @return array
	 */
	protected function getGoodTypes(bool $refresh = false): array
	{
		static $types;
		if ($types === null) {
			$types = [];
			/**
			 * @var ShoppingCartItem $good
			 */
			foreach ($this->goods as $good) {
				$types[] = $good->getItem()->getEntityType();
			}
			$types = array_unique($types);
		}
		return $types;
	}

	public function isNeedValidateComment(): bool
	{

		if (!in_array(Autopart::GOOD_ENTITY_TYPE, $this->getGoodTypes())) {
			return false;
		}

		static $needValidate;
		if ($needValidate === null) {
			$needValidate = false;
			/** @var ShoppingCartItem $good */
			foreach ($this->getGoods() as $good) {
				if ($good->getItem()->getEntityType() == Autopart::GOOD_ENTITY_TYPE) {
					/** @var Autopart $goodModel */
					$goodModel = $good->getGood();
					if (mb_strtolower($goodModel->getApCategoryId()) === 'beb0c820309d11e9af150050568041') {
						$needValidate = true;
						break;
					}
				}
			}
		}

		return $needValidate;
	}

	/**
	 * Проверяет доступность типа доставки для текущей корзины, после миграции на компонент ecommerce,
	 * перенести в форму заказа
	 * @param DeliveryInterface $delivery
	 * @return bool
	 */
	protected function checkDeliveryIsAllowed(DeliveryInterface $delivery): bool
	{
		$goodTypeDeliveryMapper = [
			Autopart::GOOD_ENTITY_TYPE => [
				DeliveryPickup::getCategory(),
			],
		];
		$allowed = true;
		$goodTypesInCart = $this->getGoodTypes();
		foreach ($goodTypeDeliveryMapper as $goodType => $deliveryTypes) {
			if (in_array($goodType, $goodTypesInCart) && !in_array($delivery::getCategory(), $deliveryTypes)) {
				$allowed = false;
				break;
			}
		}
		return $allowed;
	}

	/**
	 * @param null $types
	 * @return array
	 */
	public function getActiveDeliveries($types = null)
	{
		if ($types !== null && !empty($types) && !is_array($types)) {
			$types = [$types];
		}
		$out = [];
		/**
		 * @var DeliveryInterface $delivery
		 */
		foreach ($this->deliveries as $delivery) {
			$deliveryCategory = $delivery::getCategory();
			if ((!is_array($types) || in_array($deliveryCategory, $types)) && $this->checkDeliveryIsAllowed($delivery) && $delivery->isActive()) {
				$out[$deliveryCategory] = $delivery->toArray();
			}
		}
		return $out;
	}

	/**
	 * @param null $type
	 * @return mixed
	 */
	public function getDeliveryOptionsByType($type = null)
	{
		return $this->getDeliveryByType($type)->getData();
	}

	/**
	 * @param string $type тип способа покупки
	 * @return DeliveryInterface
	 * @throws InvalidArgumentException
	 */
	public function getDeliveryByType($type)
	{

		if (!isset($this->deliveries[$type]))
			throw new InvalidArgumentException("Delivery {$type} not defined.");

		return $this->deliveries[$type];
	}

	/**
	 * @param $type
	 * @return bool
	 */
	public function hasDeliveryType($type)
	{
		return isset($this->deliveries[$type]);
	}

}
