<?php

namespace common\models;

use common\components\GoodIsInCartTrait;
use common\components\sizes\SizeRim;
use common\interfaces\GoodInterface;
use common\models\query\DiskGoodQuery;
use domain\interfaces\GoodEntityInterface;
use http\Exception\BadMethodCallException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%disks}}".
 *
 * @property string $disk_id
 * @property string $diam
 * @property string $params
 * @property string $pn
 * @property double $pcd
 * @property double $pcd2
 * @property double $et
 * @property double $dia
 * @property string $type
 * @property string $type_name
 * @property string $color_name
 * @property string $prod
 * @property string $model
 * @property string $color
 * @property double $price
 * @property string $shops
 * @property string $delivery
 * @property integer $total
 * @property string $preorder
 * @property string $discount
 * @property string $offer
 * @property string $zapaska
 * @property string $manuf_code
 * @property double $price_discount10
 * @property string $prod_code
 * @property string $descr
 * @property string $diameter [decimal(4,1)]
 * @property string $width [decimal(4,1)]
 * @property string $origin_country [varchar(30)]
 * @property int $brand_id [int(11)]
 * @property int $model_id [int(11)]
 * @property int $variation_id [int(11)]
 */
class DiskGood extends ActiveRecord implements GoodInterface, GoodEntityInterface
{

	use GoodIsInCartTrait;

	public const GOOD_ENTITY_TYPE = 'disk';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%disks}}';
	}

	/**
	 * @inheritdoc
	 * @return DiskGoodQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new DiskGoodQuery(static::class);
	}

	public function getId()
	{
		return $this->getPrimaryKey();
	}

	public function getPrice()
	{
		throw new BadMethodCallException(sprintf('Method %s not implemented', __METHOD__));
	}

	public function getAmount()
	{
		throw new BadMethodCallException(sprintf('Method %s not implemented', __METHOD__));
	}

	public function getIsPreorder()
	{
		throw new BadMethodCallException(sprintf('Method %s not implemented', __METHOD__));
	}

	public function getEntityType()
	{
		return static::getGoodEntityType();
	}

	/**
	 * Конфликт с интерфейсом... этот метод должен возвращать связаную модель бренда, взамен метода getModelRel()
	 * @return string
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['disk_id', 'pn', 'pcd', 'pcd2', 'type_name'], 'required'],
			[['pn', 'type_name', 'delivery', 'preorder', 'discount', 'offer', 'zapaska'], 'string'],
			[['pcd', 'pcd2', 'et', 'dia', 'price', 'price_discount10'], 'number'],
			[['total'], 'integer'],
			[['disk_id', 'prod'], 'string', 'max' => 50],
			[['diam'], 'string', 'max' => 10],
			[['params'], 'string', 'max' => 40],
			[['type'], 'string', 'max' => 80],
			[['color_name', 'descr'], 'string', 'max' => 250],
			[['model'], 'string', 'max' => 32],
			[['color'], 'string', 'max' => 6],
			[['shops'], 'string', 'max' => 500],
			[['manuf_code', 'prod_code'], 'string', 'max' => 30],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'disk_id' => 'Disk ID',
			'diam' => 'Diam',
			'params' => 'Params',
			'pn' => 'Pn',
			'pcd' => 'Pcd',
			'pcd2' => 'Pcd2',
			'et' => 'Et',
			'dia' => 'Dia',
			'type' => 'Type',
			'type_name' => 'Type Name',
			'color_name' => 'Color Name',
			'prod' => 'Prod',
			'model' => 'Model',
			'color' => 'Color',
			'price' => 'Price',
			'shops' => 'Shops',
			'delivery' => 'Delivery',
			'total' => 'Total',
			'preorder' => '1 - есть предзаказ',
			'discount' => 'Discount',
			'offer' => 'Offer',
			'zapaska' => 'Zapaska',
			'manuf_code' => 'Manuf Code',
			'price_discount10' => 'Price Discount10',
			'prod_code' => 'Prod Code',
			'descr' => 'Descr',
		];
	}

	/**
	 * @return ActiveQuery
	 */
	public function getBrand()
	{
		return $this->hasOne(DiskBrand::class, ['d_producer_id' => 'brand_id'])
			->inverseOf('goods');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getModelRel()
	{
		return $this->hasOne(DiskModel::class, ['id' => 'model_id'])
			->inverseOf('goods');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getVariation()
	{
		return $this->hasOne(DiskVariation::class, ['id' => 'variation_id'])
			->inverseOf('goods');
	}

	/**
	 * @return ActiveQuery
	 */
	public function getZonePrice()
	{
		return $this->hasOne(ZonePrice::class, ['item_idx' => 'disk_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getOrderTypeStock()
	{
		return $this->hasOne(OrderTypeStock::class, ['item_idx' => 'disk_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getShopStock()
	{
		return $this->hasMany(ShopStock::class, ['item_idx' => 'disk_id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getAutoRel()
	{
		return $this->hasMany(AutoDisk::class, ['disk_id' => 'disk_id']);
	}

	/**
	 * Название размера
	 * @return mixed|string
	 */
	public function getTitle()
	{
		return $this->size->format();
	}

	/**
	 * @return SizeRim
	 */
	public function getSize()
	{
		return Yii::createObject(SizeRim::class, [$this->getSizeParams()]);
	}

	public function getSizeParams()
	{
		return [
			'diameter' => (float)$this->diameter,
			'width' => (float)$this->width,
			'pn' => (int)$this->pn,
			'pcd' => (float)$this->pcd,
			'pcd2' => (float)$this->pcd2,
			'et' => (float)$this->et,
			'cb' => (float)$this->dia,
		];
	}

	/**
	 * @return SizeRim
	 */
	public function getSizeText()
	{
		return $this->size->format();
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{
		$fields = [
			'id' => 'disk_id',
			'type' => static function ($model) {
				return static::getGoodEntityType();
			},
			'sku' => 'disk_id',
			'title',
			'brand_sku' => 'manuf_code',
			'brand_id',
			'model_id',
			'size' => 'sizeParams',
			'size_text' => 'sizeText',
			'params' => static function ($model) {
				return [];
			},
		];
		return $fields;
	}

	public static function getGoodEntityType()
	{
		return static::GOOD_ENTITY_TYPE;
	}

	public static function getGoodMaxAmountInCart(): int
	{
		return 80;
	}

	public function extraFields()
	{
		$fields = parent::extraFields();
		$fields[] = 'brand';
		$fields[] = 'model';
		$fields[] = 'variation';
		$fields['brand_short'] = static function (self $model) {
			if ($model->brand === null) {
				return null;
			}
			return $model->brand->toArrayShort();
		};

		$fields['model_short'] = static function (self $model) {
			if ($model->modelRel === null) {
				return null;
			}
			return $model->modelRel->toArrayShort();
		};
		$fields['stock'] = static function (self $model) {
			$zonePrice = $model->zonePrice !== null ? $model->zonePrice->toArray() : [];
			$orderTypeStock = $model->orderTypeStock !== null ? $model->orderTypeStock->toArray() : [];
			return array_merge($zonePrice, $orderTypeStock, [
				'packSize' => $model->getPackSize(),
			]);
		};
		return $fields;
	}

	/**
	 * @param bool $refresh
	 * @return array
	 */
	public function getPrepareSearchIndex(bool $refresh = false): array
	{
		// Кешируем сгенерированный индекс
		static $index = [];
		if (!isset($index[$this->disk_id]) || $refresh) {
			$words = ['диск', 'диски'];
			$fields = [
				'params', 'diameter', 'pn', 'pcd', 'pcd2', 'et', 'dia', 'type_name', 'color_name',
				'prod', 'model', 'color', 'manuf_code', 'prod_code',
			];
			foreach ($fields as $field) {
				$words[] = $this->{$field};
			}
			$words = preg_replace('/\s+/u', ' ', mb_strtolower(trim(implode(' ', $words))));
			$words = preg_split('/[\s,]+/u', $words);
			$index[$this->disk_id] = [];
			foreach ($words as $word) {
				$word = trim($word, '.');
				if (!in_array($word, $index[$this->disk_id]) && mb_strlen($word) >= 2) {
					$index[$this->disk_id][] = $word;
				}
			}
		}
		return $index[$this->disk_id];
	}

	public function getPackSize(): int
	{
		return 1;
	}

	public function getAddToCartQuantity(): int
	{
		return 4;
	}
}
