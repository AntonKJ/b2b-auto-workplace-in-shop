<?php

namespace common\components;


use common\components\shoppingCartMessages\GoodAvailableByPreorder;
use common\components\shoppingCartMessages\GoodNotAvailable;
use common\components\shoppingCartMessages\LimitationOnMaxPiecesPerName;
use common\components\shoppingCartMessages\Messages;
use common\components\shoppingCartMessages\NotEnoughGood;
use common\interfaces\GoodInterface;
use common\interfaces\OrderTypeGroupableInterface;
use common\interfaces\RegionEntityInterface;
use common\models\forms\ShoppingCartAddItem;
use common\models\forms\ShoppingCartRemoveItem;
use common\models\OrderTypeGroup;
use common\models\search\SearchParams;
use common\models\ShoppingCartGood;
use common\models\ZonePrice;
use domain\interfaces\GoodAvailabilityServiceInterface;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\Transaction;
use yii\web\NotFoundHttpException;
use yii\web\User;
use function count;

class ShoppingCart extends Component
{

	public const DEFAULT_ADD_QUANTITY = 4;
	public const COOKIE_NAME = 'scart';

	/**
	 * @var string class name of the repository.
	 * The model class must implement [[ShoppingCartInterface]].
	 * This property must be set.
	 */
	public $shoppingCartModelClass;
	public $shoppingCartTokenModelClass;
	public $shoppingCartGoodModelClass;
	/**
	 * @var string
	 */
	public $regionComponentName;
	/**
	 * @var string
	 */
	public $userComponentName;
	public $goodMapper = [];
	/**
	 * @var User
	 */
	protected $_userComponent;
	public $cache = 'cache';
	public $cacheDurable = 5000;
	/**
	 * @var int Ограничение на максимальное кол-ва товаров на наименование
	 */
	public $maxQuantity = 80;
	/**
	 * @var Region
	 */
	protected $_regionComponent;
	protected $_shoppingCart;
	protected $_availabilityComponent;

	public function __construct(GoodAvailabilityServiceInterface $availabilityService, array $config = [])
	{
		parent::__construct($config);
		$this->_availabilityComponent = $availabilityService;
	}

	/**
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		parent::init();

		if ($this->shoppingCartModelClass === null) {
			throw new InvalidConfigException(sprintf('%s::$regionRepositoryClass must be set.', get_class($this)));
		}
		if ($this->shoppingCartTokenModelClass === null) {
			throw new InvalidConfigException(sprintf('%s::$shoppingCartTokenModelClass must be set.', get_class($this)));
		}
		if ($this->shoppingCartGoodModelClass === null) {
			throw new InvalidConfigException(sprintf('%s::$shoppingCartGoodModelClass must be set.', get_class($this)));
		}
		// ------------------------------------------------------------------------------------------------
		if ($this->regionComponentName === null) {
			throw new InvalidConfigException(sprintf('%s::$regionComponentName must be set.', get_class($this)));
		}
		if ($this->userComponentName === null) {
			throw new InvalidConfigException(sprintf('%s::$userComponentName must be set.', get_class($this)));
		}
		if (is_string($this->cache)) {
			$this->cache = Yii::$app->get($this->cache, false);
		}
		$this->_userComponent = Yii::$app->get($this->userComponentName);
		$this->_regionComponent = Yii::$app->get($this->regionComponentName);
	}

	/**
	 * Возвращает ключи кеша для корзины
	 * @param \common\models\ShoppingCart $cart
	 * @return string[]
	 */
	protected function getCacheTagsByCart(\common\models\ShoppingCart $cart)
	{
		return ['shoppingCart', $cart->getCacheTag()];
	}

	/**
	 * @return RegionEntityInterface
	 * @throws ErrorException
	 */
	public function getRegion(): RegionEntityInterface
	{
		$region = $this->_regionComponent->current;
		if ($region === null) {
			throw new ErrorException('Не определён текущий регион!');
		}
		return $region;
	}

	/**
	 * @return OrderTypeGroupableInterface
	 * @throws ErrorException
	 * @throws Throwable
	 */
	protected function getOrderTypeGroup()
	{
		$orderTypeGroup = null;
		if (!$this->_userComponent->isGuest) {
			$orderTypeGroup = $this->_userComponent->getIdentity();
		}
		if ($orderTypeGroup === null) {
			$orderTypeGroup = $this->getRegion();
		}
		if ($orderTypeGroup === null) {
			throw new ErrorException('Не определена группа типов заказа!');
		}
		return $orderTypeGroup;
	}

	/**
	 * Возвращает типы заказов для текущей комбинации
	 * @return array
	 * @throws ErrorException
	 * @throws Throwable
	 */
	protected function getOrderTypeIntersect()
	{
		$ot = [$this->getRegion()->getOrderTypeGroupId()];
		if (!$this->_userComponent->isGuest) {
			$ot[] = $this->_userComponent->getIdentity()->getOrderTypeGroupId();
		}
		return OrderTypeGroup::calculateOrderTypeGroupIntersect($ot);
	}

	protected function getToken()
	{
		return Yii::$app->request->cookies[static::COOKIE_NAME][$this->region->id] ?? null;
	}

	protected function setToken(string $token)
	{
		return Yii::$app->response->cookies[static::COOKIE_NAME][$this->region->id] = $token;
	}

	/**
	 * Возвращает модель корзины или создает новую, если корзина ещё не
	 * существует для текущего пользователя-региона
	 * @param bool $createOnNotExist
	 * @return \common\models\ShoppingCart|null
	 * @throws Exception
	 */
	public function getCart($createOnNotExist = false)
	{
		if (null !== $this->_shoppingCart) {
			return $this->_shoppingCart;
		}
		/** @var \common\models\ShoppingCart $class */
		$class = $this->shoppingCartModelClass;
		// если пользователь залогинен
		if (!$this->_userComponent->isGuest) {
			$this->_shoppingCart = $class::find()
				->byRegion($this->region)
				->byUser($this->_userComponent->identity)
				->one();
		} else {
			$token = $this->getToken();
			if (!empty($token)) {
				$this->_shoppingCart = $class::find()
					->byRegion($this->region)
					->byToken($token)
					->one();
			}
		}
		if (null === $this->_shoppingCart && $createOnNotExist) {
			$this->_shoppingCart = $this->createCart();
		}
		return $this->_shoppingCart;
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	protected function createCart()
	{
		$class = $this->shoppingCartModelClass;
		/**
		 * @var \common\models\ShoppingCart $shoppingCart
		 */
		$shoppingCart = new $class;
		$shoppingCart->region_id = $this->region->getId();
		if (!$this->_userComponent->isGuest) {
			$shoppingCart->user_id = $this->_userComponent->identity->getId();
		}
		$shoppingCart->save(false);
		if ($this->_userComponent->isGuest) {
			$class = $this->shoppingCartTokenModelClass;
			// генерируем токен для корзины пока не найдем уникальный
			while ($class::find()->exists([
				'token' => $tokenKey = Yii::$app->security->generateRandomString(32),
			])) {
			}
			$token = new $class;
			$token->token = $tokenKey;
			$shoppingCart->link('token', $token);
			$this->setToken($token->token);
		}

		return $shoppingCart;
	}

	public function getItems()
	{
		$items = [];
		$class = $this->shoppingCartModelClass;
		if ($this->cart instanceof $class) {
			$items = $this->cart->goods;
		}
		return $items;
	}

	/**
	 * Есть товар в корзине или нет
	 * @param $type
	 * @param $id
	 * @return int|bool false if good not in cart or good quantity
	 */
	public function isGoodIdInCart($type, $id)
	{
		return isset($this->itemsByType[$type][$id]) ? (int)$this->itemsByType[$type][$id] : 0;
	}

	public function getItemsByType($refresh = false)
	{
		static $data;
		if (null === $data || $refresh) {
			$data = [];
			foreach ($this->getItems() as $itm) {
				$data[$itm->entityType][$itm->entityId] = $itm->quantity;
			}
		}
		return $data;
	}

	/**
	 * @param SearchParams $searchModel
	 * @param bool $refresh
	 * @return ShoppingCartItem[]
	 * @throws InvalidConfigException
	 */
	public function getGoods(SearchParams $searchModel, $refresh = false)
	{
		static $cartItems;
		$cacheKey = $searchModel->getFilterParams();
		ksort($cacheKey);
		$cacheKey = md5(serialize($cacheKey));
		if (!isset($cartItems[$cacheKey]) || $refresh) {
			$cartItems[$cacheKey] = [];
			// Группируем по типам товаров
			foreach ($this->getItems() as $itm) {
				$cartItems[$cacheKey][$itm->entityType][$itm->entityId] = new ShoppingCartItem($itm);
			}
			foreach ($cartItems[$cacheKey] as $type => $ids) {
				/**
				 * @var ActiveDataProvider $dataProvider
				 */
				if (!isset($this->goodMapper[$type])) {
					throw new InvalidConfigException(sprintf('%s::$goodMapper[%s] not defined.', get_class($this), $type));
				}
				//todo добавить проверку на соответствие типу
//				if (!$this->goodMapper[$type] instanceof SearchInterface)
//					throw new InvalidConfigException(get_class($this) . "::\$goodMapper[{$type}] must be type of SearchInterface.");
				$dataProvider = (new $this->goodMapper[$type]($searchModel))
					->search($searchModel->getFilterParams());
				// дополнительные условия выборки
				$dataProvider
					->query
					->limit(10000)
					->byId(array_keys($ids));
				// выключаем пагинацию
				$dataProvider->pagination = false;
				// получаем модели
				$goodsModels = $dataProvider->getModels();
				/** @var GoodInterface $goodModel */
				foreach ($goodsModels as $goodModel) {
					$cartItems[$cacheKey][$type][$goodModel->getId()]->setGood($goodModel);
				}
			}
		}
		return $cartItems[$cacheKey];
	}

	/**
	 * IDs товаров в корзине
	 * @return array
	 */
	public function getGoodKeys()
	{
		$keys = [];
		foreach ($this->getItems() as $itm) {
			$keys[] = $itm->getEntityId();
		}
		return $keys;
	}

	/**
	 * Добавляем товар в корзину
	 * @param ShoppingCartAddItem $data
	 * @param bool $replaceQuantity
	 * @return array
	 * @throws ErrorException
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 */
	public function addItem(ShoppingCartAddItem $data, $replaceQuantity = false)
	{
		$good = $data->getGood();
		$transaction = Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);
		$messages = new Messages();
		/**
		 * @var ZonePrice $zonePrice
		 */
		$zonePrice = $good
			->getZonePrice()
			->byRegionZonePrice($this->getRegion())
			->one();
		// получаем кол-во товара для типов заказа
		$orderTypeStockAvailability = $this->_availabilityComponent
			->getOrderTypeStock($good->getId(), $this->getRegion()->getZoneId());
		// вычисляем доступные для пользователя типы заказа
		$orderTypeStockAvailability = array_intersect_key($orderTypeStockAvailability, array_fill_keys($this->getOrderTypeIntersect(), null));
		// берём максимальное кол-во доступного товара
		$maxAvailableAmount = $orderTypeStockAvailability !== [] ? max($orderTypeStockAvailability) : 0;
		$maxAllowedInCartQuantity = $good::getGoodMaxAmountInCart();
		// Если установлена стоимость товара
		if ($zonePrice !== null && (float)$zonePrice->price > 0 && $maxAvailableAmount > 0) {
			try {
				// Берем корзину для пользователя
				/** @var \common\models\ShoppingCart $cart */
				$cart = $this->getCart(true);
				/** @var ShoppingCartGood $class */
				$class = $this->shoppingCartGoodModelClass;
				// Смотрим сколько товара у пользователя уже в корзине
				/** @var ShoppingCartGood $cartItem */
				$cartItem = $class::find()
					->byCart($cart)
					->byGood($good)
					->one();
				// Если ничего нет, добавляем
				if (null === $cartItem) {
					$cartItem = new $class;
					$cartItem->entity_id = $good->getId();
					$cartItem->entity_type = $good::getGoodEntityType();
					// Если включено ограничение на кол-во товара на наименование
					$quantity = $data->quantity;
					$defaultQuantity = $good->getAddToCartQuantity();
					if ($maxAvailableAmount < $defaultQuantity) {
						$defaultQuantity = $maxAvailableAmount;
					}
					$quantity = (int)$quantity === 0 ? $defaultQuantity : $quantity;
					if ($maxAllowedInCartQuantity > 0 && $quantity > $maxAllowedInCartQuantity) {
						$quantity = min($quantity, $maxAllowedInCartQuantity);
						$messages->add(new LimitationOnMaxPiecesPerName($maxAllowedInCartQuantity));
					}
					// если запрашиваемое кол-во товара больше, чем есть в наличии,
					// добавляем именно кол-во, которое в наличии и пишем комментарий
					if ($quantity > $maxAvailableAmount) {
						$messages->add(new NotEnoughGood($maxAvailableAmount));
						if ($zonePrice->isPreorder) {
							$messages->add(new GoodAvailableByPreorder());
						} else {
							$quantity = min($quantity, $maxAvailableAmount);
						}
					}
					$cartItem->quantity = $quantity;
					$cart->link('goods', $cartItem);
				} else {
					$quantityAdd = (int)$data->quantity === 0 ? 1 : $data->quantity;
					$quantity = ($replaceQuantity ? 0 : $cartItem->quantity) + $quantityAdd;
					if ($maxAllowedInCartQuantity > 0 && $quantity > $maxAllowedInCartQuantity) {
						$quantity = min($quantity, $maxAllowedInCartQuantity);
						$messages->add(new LimitationOnMaxPiecesPerName($maxAllowedInCartQuantity));
					}
					if ($quantity > $maxAvailableAmount) {
						$messages->add(new NotEnoughGood($maxAvailableAmount));
						if ($zonePrice->getIsPreorder()) {
							$messages->add(new GoodAvailableByPreorder());
						} else {
							$quantity = min($quantity, $maxAvailableAmount);
						}
					}
					$cartItem->updateAttributes([
						'quantity' => $quantity,
					]);
				}
				$cart->touch();
				if ($this->cache instanceof Cache) {
					TagDependency::invalidate($this->cache, $cart->getCacheTag());
				}
				$transaction->commit();
			} catch (\Exception $e) {
				$transaction->rollBack();
				throw $e;
			}
		} else {
			$messages->add(new GoodNotAvailable());
		}
		return [
			'messages' => $messages,
			'entity' => $cartItem ?? null,
		];
	}


	/**
	 * Удаляем товар из корзины
	 * @param ShoppingCartRemoveItem $data
	 * @return int
	 * @throws Exception
	 */
	public function removeItem(ShoppingCartRemoveItem $data)
	{
		$affectedRows = 0;
		// Берем корзину для пользователя
		/** @var \common\models\ShoppingCart $cart */
		$cart = $this->getCart();
		if ($cart !== null) {
			/** @var \common\models\ShoppingCart $class */
			$class = $this->shoppingCartGoodModelClass;
			$transaction = Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);
			try {
				$affectedRows = $class::deleteAll([
					'cart_id' => $cart->getId(),
					'entity_type' => $data->type,
					'entity_id' => $data->id,
				]);
				$cart->touch();
				if ($this->cache instanceof Cache) {
					TagDependency::invalidate($this->cache, $cart->getCacheTag());
				}
				$transaction->commit();
			} catch (Exception $e) {
				$transaction->rollBack();
				throw $e;
			}
		}
		return $affectedRows;
	}

	/**
	 * Возвращает общую сумму и кол-во товаров из корзины
	 * @param bool $refresh
	 * @return array|mixed
	 * @throws Exception
	 * @throws InvalidConfigException
	 */
	public function getSummary($refresh = false)
	{
		static $summary = null;
		if (null === $summary || $refresh) {
			/** @var \common\models\ShoppingCart $cart */
			$cart = $this->getCart();
			/**
			 * Если включен кеш и корзина существует
			 */
			if ($this->cache instanceof Cache && $cart !== null) {
				// Если нужно получить обновленные данные, инвалидируем кешь по тэгу
				if ($refresh) {
					TagDependency::invalidate($this->cache, $cart->getCacheTag());
				}
				// ключ кеша
				$key = [
					__CLASS__,
					$cart->getId(),
				];
				$summary = $this->cache->getOrSet($key, function () {
					return $this->fetchSummary();
				}, $this->cacheDurable, new TagDependency(['tags' => $this->getCacheTagsByCart($cart)]));
			} else {
				$summary = $this->fetchSummary();
			}
		}
		return $summary;
	}

	/**
	 * @return array
	 * @throws Exception
	 * @throws InvalidConfigException
	 */
	protected function fetchSummary()
	{
		$sku = 0;
		$quantity = 0;
		$price = 0.0;
		// Берем корзину для пользователя
		/** @var \common\models\ShoppingCart $cart */
		$cart = $this->getCart();
		if ($cart === null) {
			return null;
		}
		$searchModel = new SearchParams();
		$searchModel->validate();
		/**
		 * @var ShoppingCartItem[] $items
		 */
		foreach ($this->getGoods($searchModel) as $goodType => $items) {
			$sku += count($items);
			/**
			 * @var ShoppingCartItem $itm
			 */
			foreach ($items as $itm) {
				if ($itm->getGood() === null) {
					continue;
				}
				$quantity += (int)$itm->getAmountReal();
				$price += $itm->getPriceTotal();
			}
		}
		return compact('quantity', 'price', 'sku');
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	public function clear()
	{
		$affectedRows = 0;
		/** @var \common\models\ShoppingCart $cart */
		$cart = $this->getCart();
		if ($cart !== null) {
			$transaction = Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);
			try {
				/** @var \common\models\ShoppingCart $class */
				$class = $this->shoppingCartGoodModelClass;
				$affectedRows = $class::deleteAll([
					'cart_id' => $cart->getId(),
				]);
				$cart->touch();
				if ($this->cache instanceof Cache) {
					TagDependency::invalidate($this->cache, $cart->getCacheTag());
				}
				$transaction->commit();
			} catch (Exception $e) {
				$transaction->rollBack();
				throw $e;
			}
		}
		return $affectedRows;
	}

	/**
	 * @return $this
	 * @throws Exception
	 * @throws Throwable
	 */
	public function delete()
	{
		/** @var \common\models\ShoppingCart $cart */
		$cart = $this->getCart();
		if ($cart !== null) {
			$transaction = Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);
			try {
				$cart->delete();
				if ($this->cache instanceof Cache) {
					TagDependency::invalidate($this->cache, $cart->getCacheTag());
				}
				$transaction->commit();
			} catch (Exception $e) {
				$transaction->rollBack();
				throw $e;
			}
		}
		return $this;
	}

}
