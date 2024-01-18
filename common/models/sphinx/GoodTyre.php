<?php

namespace common\models\sphinx;

use common\components\GoodIsInCartTrait;
use common\interfaces\GoodInterface;
use common\models\OrderTypeStock;
use common\models\TyreBrand;
use common\models\TyreGood;
use common\models\TyreModel;
use common\models\ZonePrice;
use domain\entities\good\GoodTyreParams;
use domain\entities\SizeTyre;
use domain\interfaces\GoodEntityInterface;
use Throwable;
use yii\db\ActiveQueryInterface;
use yii\sphinx\ActiveRecord;

class GoodTyre extends ActiveRecord implements GoodInterface, GoodEntityInterface
{

	use GoodIsInCartTrait;

	public const TYPE_TYRE = 10;
	public const TYPE_DISK = 20;

	public const SALE = 1;
	public const RUNFLAT = 1;

	public const PINS_NONE = 0;
	public const PINS = 1;

	public const IS_COMMERCE = 1;

	public $good_count;
	public $selected_shops_amount;

	public $amount;
	public $price;

	/**
	 * @return string the name of the index associated with this ActiveRecord class.
	 */
	public static function indexName()
	{
		return 'myexample';
	}

	/**
	 * @inheritdoc
	 * @return GoodQuery
	 */
	public static function find()
	{
		return new GoodQuery(static::class);
	}

	public function getRetailPrice()
	{
		return $this->getZonePrice();
	}

	public function getBrand()
	{
		return $this->hasOne(TyreBrand::class, ['id' => 'brand_id']);
	}

	public function getModel()
	{
		return $this->hasOne(TyreModel::class, ['id' => 'model_id']);
	}

	/**
	 * @return ActiveQueryInterface
	 */
	public function getOrderTypeStock()
	{
		return $this->hasOne(OrderTypeStock::class, ['item_idx' => 'good_id']);
	}

	/**
	 * @return ActiveQueryInterface
	 */
	public function getZonePrice()
	{
		return $this->hasOne(ZonePrice::class, ['item_idx' => 'good_id']);
	}

	public function getId()
	{
		return $this->good_id;
	}

	/**
	 * @return mixed
	 * @deprecated use $this->getId() instead
	 */
	public function getGoodId()
	{
		return $this->getId();
	}

	public function getEntityType()
	{
		return static::getGoodEntityType();
	}

	public function getTypeCode()
	{
		return static::getGoodEntityType();
	}

	public static function getGoodEntityType()
	{
		return 'tyre';
	}

	public static function getGoodMaxAmountInCart(): int
	{
		return TyreGood::getGoodMaxAmountInCart();
	}

	public function getPackSize(): int
	{
		return 1;
	}

	/**
	 * @return GoodTyreParams
	 */
	public function getParams(): GoodTyreParams
	{
		$modelParams = $this->getModelParamsArray();
		$goodParams = $this->getGoodParamsArray();
		return new GoodTyreParams(
			$modelParams['season'],
			$modelParams['runflat'],
			$modelParams['pin'],
			$goodParams['load_index'],
			$goodParams['speed_rating'],
			$goodParams['tLong'],
			$goodParams['homologation'] ?? null,
			$goodParams['prod_year'] ?? null
		);
	}

	public function getTitle()
	{
		return $this->getSize()->format();
	}

	/**
	 * @return SizeTyre
	 */
	public function getSize(): SizeTyre
	{
		$sizeParams = $this->getGoodParamsArray();
		return new SizeTyre((float)$sizeParams['radius'], (float)$sizeParams['profile'], (float)$sizeParams['width'], (bool)$sizeParams['commerce']);
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return (float)$this->price;
	}

	/**
	 * @return int
	 * @throws Throwable
	 */
	public function getAmount()
	{
		return (int)$this->amount;
	}

	public function getIsPreorder()
	{
		return (bool)($this->getOrderTypeGroupArray()['preorder'] ?? false);
	}

	public function getZoneId()
	{
		return (int)($this->getOrderTypeGroupArray()['zone_id'] ?? null);
	}

	public function getModelParamsArray()
	{
		static $data = [];
		if (!isset($data[$this->id])) {
			$data[$this->id] = [];
			if (!empty($this->model_params)) {
				$data[$this->id] = json_decode($this->model_params, true);
			}
			if (null === $data[$this->id]) {
				$data[$this->id] = [];
			}
		}
		return $data[$this->id];
	}

	public function getGoodParamsArray()
	{
		static $data = [];
		if (!isset($data[$this->id])) {
			$data[$this->id] = [];
			if (!empty($this->good_params)) {
				$data[$this->id] = json_decode($this->good_params, true);
			}
			if (null === $data[$this->id]) {
				$data[$this->id] = [];
			}
		}
		return $data[$this->id];
	}

	public function getOfferArray()
	{
		static $data = [];
		if (!isset($data[$this->id])) {
			$data[$this->id] = [];
			if (!empty($this->offer)) {
				$data[$this->id] = json_decode($this->offer, true);
			}
		}
		return $data[$this->id];
	}

	protected function getOrderTypesArray()
	{
		static $data = [];
		if (!isset($data[$this->id])) {
			$data[$this->id] = $this->getOrderTypeGroupArray()['order_types'] ?? [];
		}
		return $data[$this->id];
	}

	protected function getOrderTypeGroupArray()
	{
		static $data = [];
		if (!isset($data[$this->id])) {
			$data[$this->id] = [];
			if (!empty($this->order_type_group)) {
				$data[$this->id] = json_decode($this->order_type_group, true);
			}
		}
		return $data[$this->id];
	}

	protected function getShopAvailabilityArray()
	{
		static $data = [];
		if (!isset($data[$this->id])) {
			$data[$this->id] = $this->getOrderTypeGroupArray()['shops'] ?? [];
		}
		return $data[$this->id];
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	public function getStock()
	{
		$offerParams = $this->getOfferArray();
		$orderTypeGroupParams = $this->getOrderTypeGroupArray();
		$data = [
			'zoneId' => (int)$this->getZoneId(),
			'price' => (float)$this->getPrice(),
			'amount' => (int)$this->getAmount(),
			'isDiscount' => (bool)($orderTypeGroupParams['discount'] ?? false),
			'isPreorder' => $this->getIsPreorder(),
			'isNew' => (bool)($offerParams['new'] ?? false),
			'isOffer' => (bool)($orderTypeGroupParams['offer'] ?? false),
			'isSale' => (bool)($orderTypeGroupParams['sale'] ?? false),
			'packSize' => $this->getPackSize(),
		];
		if ($this->isRelationPopulated('retailPrice') && $this->retailPrice !== null) {
			$data['retail'] = $this->retailPrice->toArray([
				'zoneId',
				'price',
			]);
		}
		if (null !== $this->selected_shops_amount) {
			$data['amountSelectedShops'] = (int)$this->selected_shops_amount;
		}
		return $data;
	}

	public function fields()
	{
		$fields = [

			'id' => 'goodId',

			'type' => 'typeCode',
			'typeId' => 'type',

			'sku',
			'sku1c' => 'sku_1c',
			'skuBrand' => 'sku_brand',

			'title',

			'country' => static function (self $model) {
				return $model->getCountry();
			},

			'size',
			'sizeText' => static function (self $model) {
				return $model->getSize()->format();
			},

			'params' => static function (self $model) {
				return $model->getParams()->toArray();
			},

			'brand',
			'model',

			'stock',
			'inCart' => 'isInCart',
		];

		return $fields;
	}

	/**
	 * @return string|null
	 */
	public function getCountry()
	{
		return !empty($this->country) ? $this->country : null;
	}

	public function getAddToCartQuantity(): int
	{
		return 4;
	}

}
