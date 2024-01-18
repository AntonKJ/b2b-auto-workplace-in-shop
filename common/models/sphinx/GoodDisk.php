<?php

namespace common\models\sphinx;

use common\components\GoodIsInCartTrait;
use common\interfaces\GoodInterface;
use common\models\DiskBrand;
use common\models\DiskGood;
use common\models\DiskModel;
use common\models\DiskVariation;
use common\models\OrderTypeStock;
use common\models\ZonePrice;
use domain\entities\good\GoodDiskParams;
use domain\entities\SizeDisk;
use domain\interfaces\GoodEntityInterface;
use domain\SizeDiskBuilder;
use yii\db\ActiveQueryInterface;
use yii\sphinx\ActiveRecord;

class GoodDisk extends ActiveRecord implements GoodInterface, GoodEntityInterface
{

	use GoodIsInCartTrait;

	public const TYPE_TYRE = 10;
	public const TYPE_DISK = 20;
	public const SALE = 1;

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

	/**
	 * @return ActiveQueryInterface
	 */
	public function getBrand()
	{
		return $this->hasOne(DiskBrand::class, ['d_producer_id' => 'brand_id']);
	}

	/**
	 * @return ActiveQueryInterface
	 */
	public function getModel()
	{
		return $this->hasOne(DiskModel::class, ['id' => 'model_id']);
	}

	/**
	 * @return ActiveQueryInterface
	 */
	public function getVariation()
	{
		return $this->hasOne(DiskVariation::class, ['id' => 'variationId']);
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

	public function getVariationId()
	{
		return $this->variationParamsArray['id'] ?? null;
	}

	public function getVariationParamsArray()
	{
		static $modelParams = [];
		if (!isset($modelParams[$this->id])) {
			$modelParams[$this->id] = [];
			if (!empty($this->variation_params)) {
				$modelParams[$this->id] = json_decode($this->variation_params, true);
			}
		}
		return $modelParams[$this->id];
	}

	public function getId()
	{
		return $this->getGoodId();
	}

	public function getGoodId()
	{
		return $this->good_id;
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
		return 'disk';
	}

	public static function getGoodMaxAmountInCart(): int
	{
		return DiskGood::getGoodMaxAmountInCart();
	}

	public function getPackSize(): int
	{
		return 1;
	}

	/**
	 * @return GoodDiskParams
	 */
	public function getParams(): GoodDiskParams
	{
		$goodParams = json_decode($this->good_params, true);
		if ($goodParams === null) {
			$goodParams = [];
		}
		return new GoodDiskParams($goodParams['description'] ?? null,
			$goodParams['brand_group'] ?? null);
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->getSize()->format();
	}

	/**
	 * @return SizeDisk
	 */
	public function getSize(): SizeDisk
	{
		$sizeParams = json_decode($this->good_params, true);
		return SizeDiskBuilder::instance()
			->withDiameter($sizeParams['diameter'])
			->withWidth($sizeParams['width'])
			->withPn($sizeParams['pn'])
			->withPcd($sizeParams['pcd'])
			->withEt($sizeParams['et'])
			->withCb($sizeParams['cb'])
			->build();
	}

	public function getZoneId()
	{
		return (int)($this->getOrderTypeGroupArray()['zone_id'] ?? null);
	}

	public function getPrice()
	{
		return (float)$this->price;
	}

	public function getAmount()
	{
		return (int)$this->amount;
	}

	public function getIsPreorder()
	{
		return (bool)($this->getOrderTypeGroupArray()['preorder'] ?? false);
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

	public function getOrderTypesArray()
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
			'variation',

			'stock',
			'inCart' => 'isInCart',
		];

		return $fields;
	}

	/**
	 * @return string|null
	 */
	public function getCountry(): ?string
	{
		return !empty($this->country) ? $this->country : null;
	}

	public function getAddToCartQuantity(): int
	{
		return 4;
	}

}
