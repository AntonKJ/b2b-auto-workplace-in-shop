<?php

namespace domain\repositories\ar;

use domain\entities\GeoPosition;
use domain\entities\shop\Shop;
use domain\entities\shop\ShopAndGroup;
use domain\entities\shop\ShopAndGroupCollection;
use domain\entities\shop\ShopAndGroupEntityCollectionInterface;
use domain\entities\shop\ShopCollection;
use domain\entities\shop\ShopEntityCollectionInterface;
use domain\interfaces\ShopRepositoryInterface;
use domain\interfaces\ShopSpecificationInterface;
use domain\repositories\Hydrator;
use domain\repositories\NotFoundException;
use domain\specifications\ShopSpecification;
use yii\db\ActiveRecordInterface;
use yii\db\Query;

/**
 * Class ShopRepository
 * @package domain\repositories\ar
 * @deprecated
 */
class ShopRepository extends RepositoryBase implements ShopRepositoryInterface
{

	private $hydrator;

	protected $shopModelClass;
	protected $regionCrossesModelClass;

	function __construct(ActiveRecordInterface $shopModelClass, ActiveRecordInterface $regionCrossesModelClass, Hydrator $hydrator)
	{

		$this->shopModelClass = $shopModelClass;
		$this->regionCrossesModelClass = $regionCrossesModelClass;

		$this->hydrator = $hydrator;

	}

	protected function _populateShop(array $data): Shop
	{

		$geoPosition = null;
		if(!empty($data)) {

			$pos = explode(',', $data['map_coords']);
			if (count($pos) == 2)
				$geoPosition = $this->hydrator->hydrate(GeoPosition::class, [
					'lat' => (float)$pos[0],
					'lng' => (float)$pos[1],
				]);
		}

		return $this->hydrator->hydrate(Shop::class, [

			'id' => (int)$data['shop_id'],

			'region_id' => (int)$data['region_id'],
			'network_id' => (int)$data['network_id'],
			'group_id' => (int)$data['shopgroup_id'],
			'zone_id' => (int)$data['zone_id'],

			'title' => $data['long_name'],
			'title_short' => $data['short_name'],

			'slug' => $data['url'],

			'address' => $data['address'],
			'services' => $data['service_desc'],
			'working_hours' => $data['working_hours'],

			'geo_position' => $geoPosition instanceof GeoPosition ? $geoPosition : null,

			'metro_color' => $data['metro_color'],
			'credit_cards' => $data['credit_cards'],
		]);
	}

	protected function _populateShopAndGroup(array $data): ShopAndGroup
	{
		return $this->hydrator->hydrate(ShopAndGroup::class, [
			'shop_id' => (int)$data['shop_id'],
			'group_id' => (int)$data['group_id'],
			'zone_id' => (int)$data['zone_id'],
			'title' => $data['short_name'],
		]);
	}

	public function findAllBySpecification(ShopSpecificationInterface $specification): ShopEntityCollectionInterface
	{

		$query = $this->_findBySpecification($specification);

		$data = new ShopCollection();

		foreach ($query->each() as $row)
			$data[] = $this->_populateShop($row);

		return $data;
	}

	public function findOneBySpecification(ShopSpecificationInterface $specification): Shop
	{

		$row = $this->_findBySpecification($specification)->one();
		if (null === $row)
			throw new NotFoundException();

		return $this->_populateShop($row);
	}

	protected function _findBySpecification(ShopSpecificationInterface $specification): Query
	{

		/* @var $class \common\models\Shop */
		$class = $this->shopModelClass;

		$query = $class::find();

		if (null !== $specification->isShopIdIsSet())
			$query->andWhere(($specification->isShopIdIsSet() ? '[[shop_id]] > 0' : '[[shop_id]] IS NULL OR [[shop_id]] = 0'));

		if (null !== $specification->isGroupIdIsSet())
			$query->andWhere(($specification->isGroupIdIsSet() ? '[[shopgroup_id]] > 0' : '[[shopgroup_id]] IS NULL OR [[shopgroup_id]] = 0'));

		if (null !== $specification->isActive())
			$query->andWhere(($specification->isActive() ? ['[[is_active]]' => Shop::IS_ACTIVE] : '[[is_active]] IS NULL OR [[is_active]] = 0'));

		if (null !== $specification->isNotShow())
			$query->andWhere(($specification->isNotShow() ? ['!=', '[[not_show]]', Shop::NOT_SHOW] : '[[not_show]] = 0'));

		if (null !== $specification->getRegionId())
			$query->andWhere(['[[region_id]]' => $specification->getRegionId()]);

		if (null !== $specification->getCrossesFromRegionId()) {

			$crossesClass = $this->regionCrossesModelClass;

			$shopCrossesQuery = $crossesClass::find()
				->select(['shop_id'])
				->andWhere(['[[region_id]]' => $specification->getCrossesFromRegionId()]);

			$query->andWhere(['in', '[[shop_id]]', $shopCrossesQuery]);
		}

		if (null !== $specification->getOrderBy()) {
			switch ($specification->getOrderBy()) {

				case ShopSpecification::ORDER_DEFAULT:

					$query->orderBy([
						'[[ord_num]]' => SORT_ASC,
					]);
					break;

			}
		}

		$query->asArray();

		return $query;
	}

	public function findAllShopsAndGroups($refresh = false): ShopAndGroupEntityCollectionInterface
	{

		//todo нужно избавиться от жёсткой зависимости `ShopSpecification`
		$specification = (new ShopSpecification())
			->setActive(true)
			->setGroupIdIsSet(true)
			->setShopIdIsSet(true);

		$query = $this->_findBySpecification($specification)
			->select([
				'group_id' => 'shopgroup_id',
				'shop_id',
				'zone_id',
				'short_name',
			])
			->orderBy([
				'shopgroup_id' => SORT_ASC,
				'shop_id' => SORT_ASC,
			]);

		$data = new ShopAndGroupCollection();

		foreach ($query->each() as $row)
			$data[] = $this->_populateShopAndGroup($row);

		return $data;

	}

}