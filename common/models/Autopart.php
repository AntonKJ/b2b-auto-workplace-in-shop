<?php

namespace common\models;

use common\components\GoodIsInCartTrait;
use common\interfaces\GoodInterface;
use common\models\query\AutopartCategoryQuery;
use common\models\query\AutopartQuery;
use common\models\query\OrderTypeStockQuery;
use common\models\query\ZonePriceQuery;
use domain\interfaces\GoodEntityInterface;
use lib\classes\Helpers\InflectorHelper;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * This is the model class for table "{{%autoparts}}".
 *
 * @property ZonePrice $zonePrice
 * @property AutopartCategory $apCategory
 */
class Autopart extends ActiveRecord implements GoodInterface, GoodEntityInterface
{

	use GoodIsInCartTrait;

	public const GOOD_ENTITY_TYPE = 'autopart';

	/**
	 * @var int|null
	 */
	protected $_amount;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%autoparts}}';
	}

	public static function find()
	{
		return new AutopartQuery(static::class);
	}

	public static function getGoodEntityType(): string
	{
		return static::GOOD_ENTITY_TYPE;
	}

	public static function getGoodMaxAmountInCart(): int
	{
		return 1000;
	}

	public function getEntityType()
	{
		return static::getGoodEntityType();
	}

	/**
	 * @return ActiveQueryInterface|AutopartCategoryQuery
	 */
	public function getApCategory()
	{
		return $this->hasOne(AutopartCategory::class, ['apcategory_id' => 'apcategory_id']);
	}

	/**
	 * @return ActiveQueryInterface|ZonePriceQuery
	 */
	public function getZonePrice()
	{
		return $this->hasOne(ZonePrice::class, ['item_idx' => 'autopart_id']);
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->autopart_id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getBrand(): string
	{
		return $this->brand;
	}

	/**
	 * @return null
	 */
	public function getModel()
	{
		return null;
	}

	/**
	 * @return string
	 */
	public function getBrandSku(): string
	{
		return $this->manuf_code;
	}

	/**
	 * @return string
	 */
	public function getApCategoryId(): string
	{
		return $this->apcategory_id;
	}

	/**
	 * @return int|null
	 */
	public function getAmount(): int
	{
		return $this->_amount ?? 0;
	}

	/**
	 * @param int|null $amount
	 * @return Autopart
	 */
	public function setAmount(?int $amount): Autopart
	{
		$this->_amount = $amount === 0 ? null : $amount;
		return $this;
	}

	public function getStock(): array
	{
		return [
			'amount' => $this->getAmount(),
			'price' => (int)($this->zonePrice->price ?? 0),
			'packSize' => $this->getPackSize(),
		];
	}

	public function getPackSize(): int
	{
		return ($ps = (int)$this->pack_size) === 0 ? 1 : $ps;
	}

	public function getAddToCartQuantity(): int
	{
		return $this->apCategory !== null ? $this->apCategory->getAddToCartQuantity() : 1;
	}

	public function fields()
	{
		return [
			'id',
			'type' => static function (self $model) {
				return $model::getGoodEntityType();
			},
			'title',
			'brand',
			'brandSku',
			'stock',
			'apCategoryId',
			'inCart' => 'isInCart',
			'imageUrl',
		];
	}

	public function extraFields()
	{
		return [
			'apCategory',
		];
	}

	public function getPrice()
	{
		return $this->isRelationPopulated('zonePrice') ? $this->zonePrice->price : 0.0;
	}

	public function getIsPreorder()
	{
		return false;
	}

	/**
	 * @return OrderTypeStockQuery|ActiveQuery
	 */
	public function getOrderTypeStock()
	{
		return $this->hasOne(OrderTypeStock::class, ['item_idx' => 'autopart_id']);
	}

	public function getImageUrl(): ?string
	{
		if (empty($this->getBrand()) || empty($this->getBrandSku())) {
			return null;
		}

		return static::getImagePathByBrandAndManufacturerCode($this->getBrand(), $this->getBrandSku(),
			$this->getApCategory() !== null ? $this->apCategory->images_version : null);
	}

	public static function getImagePathByBrandAndManufacturerCode(string $brand, string $manufacturerCode, ?int $imagesVersion = null): string
	{

		$brand = Inflector::slug(mb_strtolower($brand));
		$manufacturerCode = Inflector::slug(mb_strtolower($manufacturerCode));

		return Yii::$app->media->getStorageUrl(implode('/', [
			'catalog',
			'autoparts',
			$brand,
			$manufacturerCode . '.jpg' . (null !== $imagesVersion ? '?' . $imagesVersion : null),
		]));
	}
}
