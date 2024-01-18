<?php

namespace common\models;

use common\interfaces\RegionEntityInterface;
use common\models\query\RegionQuery;
use domain\entities\GeoPosition;
use domain\traits\DeliveryDaysTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use function count;
use function is_array;

/**
 * This is the model class for table "{{%regions}}".
 *
 * @property integer $region_id
 * @property integer $region_group_id
 * @property string $name
 * @property integer $ord_num
 * @property string $phone
 * @property integer $zone_id
 * @property string $url_frag
 * @property string $region_text
 * @property string $name_declination
 * @property string $phone2
 * @property string $map_zoom
 * @property string $map_coords
 * @property integer $is_reg_movement
 * @property string $mail_4notify
 * @property integer $is_show_on_menu
 * @property string $zone_type
 * @property integer $price2_from_region_id
 * @property integer $alt_zone_id
 * @property integer $shops_from_region_id
 * @property integer $region_deliverytype_id
 * @property integer $reg_area_id
 * @property string $delivery_note
 * @property integer $delivery_days
 * @property integer $order_type_group_id
 * @property int $parent_id [int(11) unsigned]
 * @property bool $type [tinyint(4)]
 * @property bool $from_evolution [tinyint(1)]
 * @property string $phone_info [varchar(255)]
 * @property bool $is_active [tinyint(1)]
 *
 * @property string[] $emails список адресов эл. почты для уведомления
 * @property bool $isRegionInMoscowGroup текущий регион относиться к Московской группе
 * @property bool $hasMovementToRegion текущий регион поддерживает перемещение
 * @property int|null $regionIdForShops ID региона для магазинов
 */
class Region extends ActiveRecord implements RegionEntityInterface
{

	use DeliveryDaysTrait;

	public const SCENARIO_ADMIN = 'admin';

	public const ZONE_TYPE_WWW = 'www';
	public const ZONE_TYPE_CC = 'cc';
	public const ZONE_TYPE_B2B = 'b2b';

	public const IS_SHOW_ON_MENU = 1;
	public const IS_ACTIVE = 1;

	public function getDeliveryDaysMask(): int
	{
		return (int)$this->delivery_days > 0 ? (int)$this->delivery_days : RegionEntityInterface::DELIVERY_DAYS_ALL;
	}

	/**
	 * @inheritdoc
	 */
	public function getOrderTypeGroupId()
	{
		return ($groupId = (int)$this->order_type_group_id) > 0 ? $groupId : null;
	}

	public function getOrderTypeGroup()
	{
		return $this->hasOne(OrderTypeGroup::class, ['id' => 'order_type_group_id']);
	}

	public function getId()
	{
		return $this->region_id;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%regions}}';
	}

	public function scenarios()
	{

		$scenarios = parent::scenarios();

		$scenarios[static::SCENARIO_ADMIN] = ['order_type_group_id', 'shops_from_region_id'];

		return $scenarios;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['order_type_group_id'], 'required'],
			[['order_type_group_id'], 'integer'],
			[['order_type_group_id'], 'exist', 'targetClass' => OrderTypeGroup::class, 'targetAttribute' => 'id', 'allowArray' => true],

			[['shops_from_region_id'], 'integer'],
			[['shops_from_region_id'], 'exist', 'targetClass' => Region::class, 'targetAttribute' => 'region_id'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'region_id' => 'Region ID',
			'region_group_id' => 'Region Group ID',
			'name' => 'Name',
			'ord_num' => 'Ord Num',
			'phone' => 'Phone',
			'zone_id' => 'Zone ID',
			'url_frag' => 'Url Frag',
			'region_text' => 'Region Text',
			'name_declination' => 'Name Declination',
			'phone2' => 'Phone2',
			'map_zoom' => 'Map Zoom',
			'map_coords' => 'Map Coords',
			'is_reg_movement' => 'Is Reg Movement',
			'mail_4notify' => 'Mail 4notify',
			'is_show_on_menu' => 'Is Show On Menu',
			'zone_type' => 'Zone Type',
			'price2_from_region_id' => 'Price2 From Region ID',
			'alt_zone_id' => 'Alt Zone ID',
			'shops_from_region_id' => 'Shops From Region ID',
			'region_deliverytype_id' => 'Region Deliverytype ID',
			'reg_area_id' => 'Reg Area ID',
			'delivery_note' => 'Delivery Note',
			'delivery_days' => 'Delivery Days',
			'order_type_group_id' => 'Группа типов заказа',
		];
	}

	/**
	 * @inheritdoc
	 * @return RegionQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new RegionQuery(static::class);
	}

	/**
	 * @return bool
	 */
	public function getIsRegionInMoscowGroup(): bool
	{
		return Yii::$app->region->isRegionInMoscowGroup($this);
	}

	/**
	 * @return bool
	 */
	public function getIsRegionZoneTypeB2B(): bool
	{
		return Yii::$app->region->isRegionZoneTypeB2B($this);
	}

	/**
	 * @return bool
	 */
	public function getIsRegionZoneTypeCC(): bool
	{
		return Yii::$app->region->isRegionZoneTypeCC($this);
	}

	/**
	 * Есть ли перемещение товара из других регионов в этот, не использовать
	 * @return bool
	 * @deprecated используй isMovementToRegion
	 */
	public function getHasMovementToRegion()
	{
		return $this->isMovementToRegion();
	}

	/**
	 * Есть ли перемещение товара из других регионов в этот
	 * @return bool
	 */
	public function isMovementToRegion(): bool
	{
		return (int)$this->is_reg_movement > 0;
	}

	/**
	 * Возвращает ID региона для подгрузки магазинов
	 * @return int|null
	 */
	public function getRegionIdForShops()
	{
		return ($id = (int)$this->shops_from_region_id) > 0 ? $id : null;
	}

	/**
	 * Возвращает актуальное zone_id для региона с учетом альтернативной зоны
	 * @return int
	 */
	public function getPriceZoneId()
	{
		return ($zId = (int)$this->alt_zone_id) > 0 ? $zId : (int)$this->zone_id;
	}

	/**
	 * Возвращает zone_id
	 * @return int
	 */
	public function getZoneId()
	{
		return (int)$this->zone_id;
	}

	public function getZoneType()
	{
		return mb_strtolower($this->zone_type);
	}

	public function getAltZoneId(): int
	{
		return (int)$this->alt_zone_id;
	}

	/**
	 * @return int|null
	 */
	public function getDeliveryTypeId(): ?int
	{
		return (int)$this->region_deliverytype_id;
	}

	public function getIsDeliveryTypePek()
	{
		return $this->deliveryTypeId == Yii::$app->delivery->deliveryIdPek;
	}

	/**
	 * @return null|string
	 */
	public function getDeliveryNotes(): ?string
	{
		return $this->delivery_note;
	}

	public function getTitle(): string
	{
		return $this->name;
	}

	/**
	 * @return GeoPosition|null
	 */
	public function getGeoPosition(): ?GeoPosition
	{
		static $cache = [];

		if (!isset($cache[$this->getId()])) {

			$cache[$this->getId()] = null;

			if (!empty($this->map_coords)) {

				$cache[$this->getId()] = StringHelper::explode($this->map_coords, ',', true, true);

				$cache[$this->getId()] = array_map('floatval', $cache[$this->getId()]);

				$cache[$this->getId()] = array_filter($cache[$this->getId()], function ($v) {
					return $v !== 0.0;
				});

				if (count($cache[$this->getId()]) !== 2) {
					$cache[$this->getId()] = null;
				} else {
					$cache[$this->getId()] = new GeoPosition($cache[$this->getId()][0], $cache[$this->getId()][1]);
				}
			}
		}

		return $cache[$this->getId()];
	}

	public function getPhoneParts(): array
	{

		static $phone;

		if (!isset($phone[$this->phone])) {

			$phone[$this->phone] = [];
			if (preg_match('/^(?<code>\+?\d+)\s*\(?(?<city>\d{3,4})\)?\s*(?<number>[\d]+[\d\-\s]+)$/ui', trim($this->phone), $matches)) {

				$phone[$this->phone] = [
					'code' => $matches['code'],
					'city' => $matches['city'],
					'number' => trim(str_replace(' ', '-', $matches['number']), '-'),
				];
			}
		}

		return $phone[$this->phone];
	}

	public function getPhone()
	{

		$phone = $this->getPhoneParts();
		if ([] === $phone) {
			return null;
		}

		$phone = array_merge([
			'code' => null,
			'city' => null,
			'number' => null,
		], $phone);

		return trim(is_array($phone) ? "{$phone['code']} {$phone['city']} {$phone['number']}" : $this->phone);

	}

	/**
	 * @return string[]
	 */
	public function getEmails(): array
	{
		return StringHelper::explode($this->mail_4notify, ';', true, true);
	}

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'id',
			'priceZoneId',
			'title',
			'phone' => static function (self $model) {
				return $model->getPhone();
			},
			'phoneParts',
			'email' => 'emails',
			'delivery' => static function (self $model) {
				return [
					'days' => $model->getDeliveryDays(),
					'notes' => $model->getDeliveryNotes(),
				];
			},
			'geoPosition' => static function (self $model) {
				$geoPosition = $model->getGeoPosition();
				return $geoPosition instanceof GeoPosition ? [$geoPosition->getLat(), $geoPosition->getLng()] : null;
			},
		];
	}

}
