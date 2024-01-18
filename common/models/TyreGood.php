<?php

namespace common\models;

use BadMethodCallException;
use common\components\GoodIsInCartTrait;
use common\interfaces\GoodInterface;
use common\models\query\TyreGoodQuery;
use domain\interfaces\GoodEntityInterface;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%assort}}".
 *
 * @property string $idx
 * @property string $season
 * @property string $prod_code
 * @property string $serie
 * @property string $in_type
 * @property string $cc
 * @property string $rad
 * @property string $width
 * @property string $pr
 * @property string $sz
 * @property double $tlong
 * @property string $p_t
 * @property integer $price
 * @property string $pin
 * @property string $shops
 * @property string $delivery
 * @property integer $total
 * @property string $preorder
 * @property string $discount
 * @property string $offer
 * @property string $manuf_code
 * @property integer $max_av
 * @property string $runflat
 * @property string $code_1c
 * @property string $origin_country
 * @property float $amount
 * @property string $homologation
 */
class TyreGood extends \yii\db\ActiveRecord implements GoodInterface, GoodEntityInterface
{

	use GoodIsInCartTrait;

	public const GOOD_ENTITY_TYPE = 'tyre';

	public const SEASON_SUMMER = 'S';
	public const SEASON_WINTER = 'W';

	public const PINS_YES = 'Y';
	public const PINS_NO = 'N';

	public const RUNFLAT = 1;
	public const DISCOUNT = 1;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%assort}}';
	}

	public function getEntityType()
	{
		return static::getGoodEntityType();
	}

	public static function getGoodMaxAmountInCart(): int
	{
		return 80;
	}

	public function getPackSize(): int
	{
		return 1;
	}

	public function getAddToCartQuantity(): int
	{
		return 4;
	}

	/**
	 * @inheritdoc
	 * @return TyreGoodQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new TyreGoodQuery(static::class);
	}

	public function getId()
	{
		return $this->idx;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBrand()
	{
		return $this->hasOne(TyreBrand::class, ['code' => 'prod_code'])
			->inverseOf('goods');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getModel()
	{
		return $this
			->hasOne(TyreModel::class, [
				'prod_code' => 'prod_code',
				'code' => 'p_t',
			])
			->inverseOf('goods');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getZonePrice()
	{
		return $this->hasOne(ZonePrice::class, ['item_idx' => 'idx']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrderTypeStock()
	{
		return $this->hasOne(OrderTypeStock::class, ['item_idx' => 'idx']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getShopStock()
	{
		return $this->hasMany(ShopStock::class, ['item_idx' => 'idx']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAutoRel()
	{
		return $this->hasMany(AutoTyre::class, ['sz' => 'sz']);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['idx', 'cc', 'p_t', 'price'], 'required'],
			[['season', 'serie', 'cc', 'pin', 'delivery', 'preorder', 'discount', 'offer', 'runflat'], 'string'],
			[['tlong'], 'number'],
			[['price', 'total', 'max_av'], 'integer'],
			[['idx', 'manuf_code'], 'string', 'max' => 50],
			[['prod_code', 'origin_country'], 'string', 'max' => 30],
			[['in_type'], 'string', 'max' => 7],
			[['rad', 'pr'], 'string', 'max' => 5],
			[['width'], 'string', 'max' => 8],
			[['sz'], 'string', 'max' => 20],
			[['p_t'], 'string', 'max' => 100],
			[['shops'], 'string', 'max' => 500],
			[['code_1c'], 'string', 'max' => 60],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'idx' => 'Idx',
			'season' => 'Season',
			'prod_code' => 'Prod Code',
			'serie' => 'Serie',
			'in_type' => 'In Type',
			'cc' => 'Cc',
			'rad' => 'Rad',
			'width' => 'Width',
			'pr' => 'Pr',
			'sz' => 'Sz',
			'tlong' => 'длина окружности',
			'p_t' => 'P T',
			'price' => 'Price',
			'pin' => 'Pin',
			'shops' => 'Shops',
			'delivery' => 'Delivery',
			'total' => 'Total',
			'preorder' => 'Preorder',
			'discount' => 'Discount',
			'offer' => 'Offer',
			'manuf_code' => 'Manuf Code',
			'max_av' => 'Max Av',
			'runflat' => 'Runflat',
			'code_1c' => 'Code 1c',
			'origin_country' => 'Origin Country',
		];
	}

	public function getLoadIndexText()
	{
		return static::getLoadIndexTextByIndex($this->in_type);
	}

	public static function getLoadIndexTextByIndex($index)
	{
		return ArrayHelper::getValue(static::getLoadIndexOptions(), $index);
	}

	public static function getLoadIndexOptions()
	{
		return [
			69 => 325,
			70 => 335,
			71 => 345,
			72 => 355,
			73 => 365,
			74 => 376,
			75 => 387,
			76 => 400,
			77 => 412,
			78 => 425,
			79 => 437,
			80 => 450,
			81 => 462,
			82 => 475,
			83 => 487,
			84 => 500,
			85 => 515,
			86 => 530,
			87 => 545,
			88 => 560,
			89 => 580,
			90 => 600,
			91 => 615,
			92 => 630,
			93 => 650,
			94 => 670,
			95 => 690,
			96 => 710,
			97 => 730,
			98 => 750,
			99 => 775,
			100 => 800,
			101 => 825,
			102 => 850,
			103 => 875,
			104 => 900,
			105 => 925,
			106 => 950,
			107 => 975,
			108 => 1000,
			109 => 1030,
			110 => 1060,
			111 => 1090,
			112 => 1120,
			113 => 1150,
			114 => 1180,
			115 => 1215,
			116 => 1250,
			117 => 1285,
			118 => 1320,
			119 => 1360,
			120 => 1400,
			121 => 1450,
			122 => 1500,
			123 => 1550,
			124 => 1600,
			125 => 1650,
			126 => 1700,
			127 => 1750,
			128 => 1800,
			129 => 1850,
			130 => 1900,
			131 => 1950,
			132 => 2000,
			133 => 2060,
			134 => 2120,
			135 => 2180,
			136 => 2240,
			137 => 2300,
			138 => 2360,
			139 => 2430,
			140 => 2500,
			141 => 2570,
			142 => 2650,
			143 => 2720,
			144 => 2800,
			145 => 2900,
			146 => 3000,
			147 => 3070,
			148 => 3150,
			149 => 3250,
			150 => 3350,
			151 => 3450,
			152 => 3550,
			153 => 3650,
			154 => 3750,
			156 => 4000,
			158 => 4260,
			159 => 4375,
			160 => 4500,
			161 => 4620,
			162 => 4750,
			163 => 4875,
			164 => 5000,
			165 => 5150,
		];
	}

	public function getSpeedRatingText()
	{
		return static::getSpeedRatingTextByIndex($this->cc);
	}

	public static function getSpeedRatingTextByIndex($index)
	{
		return ArrayHelper::getValue(static::getSpeedRatingOptions(), $index);
	}

	static function getSpeedRatingOptions()
	{
		return [
			'J' => 100,
			'K' => 110,
			'L' => 120,
			'M' => 130,
			'N' => 140,
			'P' => 150,
			'Q' => 160,
			'R' => 170,
			'S' => 180,
			'T' => 190,
			'U' => 200,
			'H' => 210,
			'Z' => '>= 240',
			'ZR' => '>= 240',
			'V' => 240,
			'W' => 270,
			'Y' => 300,
		];
	}

	public function getSeasonText()
	{
		return ArrayHelper::getValue(self::getSeasonOptions(), $this->season);
	}

	public static function getSeasonOptions()
	{
		return [
			self::SEASON_SUMMER => 'Лето',
			self::SEASON_WINTER => 'Зима',
		];
	}

	public function getSeasonCode()
	{
		return ArrayHelper::getValue(static::getSeasonCodeOptions(), $this->season);
	}

	public static function getSeasonCodeOptions()
	{
		return [
			self::SEASON_SUMMER => 'summer',
			self::SEASON_WINTER => 'winter',
		];
	}

	public function getPinText()
	{
		return ArrayHelper::getValue(self::getPinOptions(), $this->pin);
	}

	public static function getPinOptions()
	{
		return [
			self::PINS_YES => 'Шипы',
			self::PINS_NO => '',
		];
	}

	public function getIsDiscount(): bool
	{
		return (int)$this->discount === static::DISCOUNT;
	}

	public function getHasPin(): bool
	{
		return mb_strtoupper($this->pin) === static::PINS_YES;
	}

	/**
	 * Коммерческая шина
	 * @return bool
	 */
	public function getIsCommerce(): bool
	{
		return !empty($this->rad) && stripos($this->rad, 'c') !== false;
	}

	/**
	 * Название размера
	 * @return mixed|string
	 */
	public function getTitle()
	{
		return mb_strtoupper("{$this->width}/{$this->pr} {$this->rad} {$this->in_type}{$this->cc}");
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{
		$fields = [
			'id' => 'idx',
			'type' => static function (self $model) {
				return static::getGoodEntityType();
			},
			'sku' => 'idx',
			'title',
			'brand_sku' => 'manuf_code',
			'brand_code' => static function (self $model) {
				return mb_strtolower($model->prod_code);
			},
			'country' => 'origin_country',
			'model_code' => static function (self $model) {
				return mb_strtolower($model->p_t);
			},
			'size' => function (self $model) {
				return [
					'width' => (float)$model->width,
					'profile' => (float)$model->pr,
					'radius' => (float)str_replace('R', '', $this->rad),
					'commerce' => $this->getIsCommerce(),
				];
			},
			'params' => static function (self $model) {
				return [
					'season' => [
						'id' => mb_strtolower($model->season),
						'text' => $model->getSeasonText(),
						'code' => $model->getSeasonCode(),
					],
					'runflat' => $model->getIsRunflat(),
					'pins' => $model->getHasPin(),
					'load_index' => [
						'index' => $model->in_type,
						'max_weight' => $model->getLoadIndexText(),
					],
					'speed_rating' => [
						'index' => $model->cc,
						'max_speed' => $model->getSpeedRatingText(),
					],
					'tlong' => (float)$model->tlong,
				];
			},
		];
		return $fields;
	}

	public static function getGoodEntityType()
	{
		return static::GOOD_ENTITY_TYPE;
	}

	public function extraFields()
	{
		$fields = parent::extraFields();
		$fields[] = 'brand';
		$fields[] = 'model';
		$fields['brand_short'] = static function (self $model) {
			if ($model->brand === null) {
				return null;
			}
			return $model->brand->toArrayShort();
		};
		$fields['model_short'] = static function (self $model) {
			if ($model->model === null) {
				return null;
			}
			return $model->model->toArray([
				'id',
				'name',
				'code',
				'brand_code',
				'type',
				'logoUrl',
				'url',
				'params',
			]);
		};
		$fields['stock'] = static function (self $model) {
			$zonePrice = $model->zonePrice !== null ? $model->zonePrice->toArray() : [];
			$orderTypeStock = $model->orderTypeStock !== null ? $model->orderTypeStock->toArray() : [];
			return array_merge($zonePrice, $orderTypeStock, [
				'packSize' => $this->getPackSize(),
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
		if (!isset($index[$this->primaryKey]) || $refresh) {
			$words = ['шина', 'шины'];
			$fields = ['prod_code', 'seasonText', 'pinText', 'width', 'pr', 'rad', 'sz', 'p_t', 'manuf_code', 'in_type'];
			foreach ($fields as $field) {
				$words[] = $this->{$field};
			}
			$words[] = $this->in_type . $this->cc;
			if ($this->getIsRunflat()) {
				$words[] = 'rnflt';
				$words[] = 'flt';
				$words[] = 'fltrn';
				$words[] = 'runflat';
				$words[] = 'flatrun';
				$words[] = 'run flat';
				$words[] = 'flat run';
				$words[] = 'флетран';
				$words[] = 'флэтран';
				$words[] = 'ранфлет';
				$words[] = 'ранфлэт';
			}
			$words = preg_replace('/\s+/u', ' ', mb_strtolower(trim(implode(' ', $words))));
			$words = preg_split('/[\s,]+/u', $words);
			$index[$this->primaryKey] = [];
			foreach ($words as $word) {
				$word = trim($word, '.');
				if (!in_array($word, $index[$this->primaryKey]) && mb_strlen($word) >= 2) {
					$index[$this->primaryKey][] = $word;
				}
			}
		}
		return $index[$this->primaryKey];
	}

	public function getIsRunflat(): bool
	{
		return (int)$this->runflat === static::RUNFLAT;
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

	public function getHomologation()
	{
		return $this->Homologation;
	}

	public function getHasHomologation(): bool
	{
		return !empty($this->homologation);
	}

}
