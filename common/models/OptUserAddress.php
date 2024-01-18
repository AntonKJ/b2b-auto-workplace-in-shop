<?php

namespace common\models;

use common\models\query\OptUserAddressQuery;
use common\models\query\OptUserQuery;
use ReflectionException;
use myexample\ecommerce\deliveries\DeliveryCityRegion;
use myexample\ecommerce\DeliveryCityModelInterface;
use myexample\ecommerce\Ecommerce;
use myexample\ecommerce\GeoPosition;
use myexample\ecommerce\MetroStationModelInterface;
use myexample\ecommerce\OrderTypeCollection;
use myexample\ecommerce\OrderTypeModelInterface;
use myexample\ecommerce\PoiInterface;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use function is_array;

/**
 * @property integer $id
 * @property integer $opt_user_id
 * @property string $type
 * @property string $hash
 * @property array $address
 * @property string $updated_at
 *
 * @property ActiveQuery|OptUserQuery $user
 */
class OptUserAddress extends ActiveRecord
{

	public const USE_IN_API = 1;

	public function generateHash()
	{

		$hashParts = [
			$this->opt_user_id,
			$this->type,
			$this->prepareAddressForHashing(),
		];

		return md5(implode('|', $hashParts));
	}

	protected function prepareAddressForHashing()
	{

		$prepareFunc = static function ($data) use (&$prepareFunc) {
			if (!is_array($data)) {
				$data = [$data];
			} else {
				ksort($data);
			}
			$out = [];
			foreach ($data as $f) {
				$out[] = is_array($f) ? $prepareFunc($f) : $f;
			}
			return implode('|', $out);
		};
		return $prepareFunc($this->address);
	}

	/**
	 * @inheritdoc
	 * @return OptUserAddressQuery
	 */
	public static function find()
	{
		return new OptUserAddressQuery(static::class);
	}

	/**
	 * @return ActiveQuery|OptUserQuery
	 */
	public function getUser()
	{
		return $this->hasOne(OptUser::class, ['id' => 'opt_user_id']);
	}

	/**
	 * @return $this
	 */
	public function touchUpdatedAt()
	{
		$this->updateAttributes([
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		return $this;
	}

	/** @inheritdoc */
	public static function tableName()
	{
		return '{{%opt_users_address}}';
	}

	public function isUseInApi(): bool
	{
		return ((int)$this->use_in_api === static::USE_IN_API);
	}

	public function fields()
	{
		return [
			'id',
			'hash',
			'address',
			'useInApi' => static function (self $model) {
				return $model->isUseInApi();
			},
		];
	}

	public function getGeoPosition(): ?GeoPosition
	{
		if ($this->type !== DeliveryCityRegion::getCategory()) {
			return null;
		}

		if (!isset($this->address['coords']) || !is_array($this->address['coords']) || count($this->address['coords']) !== 2) {
			return null;
		}

		$coords = array_values($this->address['coords']);
		return new GeoPosition($coords[0], $coords[1]);
	}

	/**
	 * @return OrderTypeCollection
	 * @throws Throwable
	 */
	protected function fetchOrderTypes(): OrderTypeCollection
	{

		$geoPosition = $this->getGeoPosition();
		if (!$geoPosition instanceof GeoPosition) {
			return new OrderTypeCollection();
		}

		/** @var Ecommerce $ecommerce */
		$ecommerce = Yii::$app->ecommerce;

		/** @var Region $region */
		$region = Yii::$app->region->current;

		/** @var OptUser $user */
		$user = Yii::$app->getUser()->getIdentity();

		$orderTypeIds = $ecommerce->getOrderTypeRepository()
			->getOrderTypeIdsByGroupsIntersect([
				$region->getOrderTypeGroupId(),
				$user->getOrderTypeGroupId(),
			]);

		$orderTypeCollection = $ecommerce->getOrderTypeRepository()
			->getOrderTypesCityRegionCategoryByIdsOrderedByPriority($orderTypeIds, $geoPosition);

		// Если нет зон с текущей точкой, ставим все доступные зоны для текущей доставки
		if ($orderTypeCollection->count() === 0) {

			$closestPoi = $ecommerce->getDeliveryCityRepository()
				->getOneClosestByOrderTypeId($orderTypeIds, $geoPosition, DeliveryCityModelInterface::DEFAULT_AREA_RADIUS);

			$closestMetro = $ecommerce->getMetroRepository()
				->getOneClosestByOrderTypeId($orderTypeIds, $geoPosition, MetroStationModelInterface::DEFAULT_AREA_RADIUS);

			if ($closestPoi === null || ($closestMetro !== null && $closestPoi->getDistance() > $closestMetro->getDistance())) {
				$closestPoi = $closestMetro;
			}

			$orderTypeCollection = new OrderTypeCollection();

			if ($closestPoi instanceof PoiInterface) {

				$orderType = $closestPoi
					->getPoiRepository()
					->getOneOrderTypeByPoi($closestPoi, $orderTypeIds);

				if ($orderType instanceof OrderTypeModelInterface) {
					$orderTypeCollection->add($orderType);
				}
			}
		}

		return $orderTypeCollection;
	}

	/**
	 * @return PoiInterface|null
	 * @throws Throwable
	 * @throws ReflectionException
	 */
	public function getClosestPoi(): ?PoiInterface
	{

		/** @var PoiInterface|null $closestPoi */
		$closestPoi = null;

		$geoPosition = $this->getGeoPosition();
		if (!$geoPosition instanceof GeoPosition) {
			return $closestPoi;
		}

		/** @var Ecommerce $ecommerce */
		$ecommerce = Yii::$app->ecommerce;

		/** @var Region $region */
		$region = Yii::$app->region->current;

		/** @var OptUser $user */
		$user = Yii::$app->getUser()->getIdentity();

		$orderTypeIds = $ecommerce->getOrderTypeRepository()
			->getOrderTypeIdsByGroupsIntersect([
				$region->getOrderTypeGroupId(),
				$user->getOrderTypeGroupId(),
			]);

		$orderTypeCollection = $ecommerce->getOrderTypeRepository()
			->getOrderTypesCityRegionCategoryByIdsOrderedByPriority($orderTypeIds, $geoPosition);

		if ($orderTypeCollection->count() > 0) {
			$orderTypeIds = [];
			/** @var OrderTypeModelInterface $orderType */
			foreach($orderTypeCollection as $orderType) {
				$orderTypeIds[] = $orderType->getId();
			}
		}

		$closestPoi = $ecommerce->getDeliveryCityRepository()
			->getOneClosestByOrderTypeId($orderTypeIds, $geoPosition, $orderTypeCollection->count() === 0 ? DeliveryCityModelInterface::DEFAULT_AREA_RADIUS : null);

		$closestMetro = $ecommerce->getMetroRepository()
			->getOneClosestByOrderTypeId($orderTypeIds, $geoPosition, $orderTypeCollection->count() === 0 ? MetroStationModelInterface::DEFAULT_AREA_RADIUS : null);

		if ($closestPoi === null || ($closestMetro !== null && $closestPoi->getDistance() > $closestMetro->getDistance())) {
			$closestPoi = $closestMetro;
		}

		return $closestPoi;
	}

	/**
	 * @return OrderTypeCollection
	 * @throws Throwable
	 */
	public function getOrderTypes(): OrderTypeCollection
	{
		$geoPosition = (string)$this->getGeoPosition();
		static $cache = [];
		if (!isset($cache[$geoPosition])) {
			$cache[$geoPosition] = $this->fetchOrderTypes();
		}
		return $cache[$geoPosition];
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	public function getOrderTypesIds(): array
	{
		$ids = [];
		/** @var OrderTypeModelInterface $orderType */
		foreach ($this->getOrderTypes() as $orderType) {
			$ids[] = $orderType->getId();
		}
		return $ids;
	}

}
