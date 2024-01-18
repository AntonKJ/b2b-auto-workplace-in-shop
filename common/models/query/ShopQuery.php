<?php

namespace common\models\query;

use common\interfaces\RegionEntityInterface;
use common\models\Shop;

/**
 * This is the ActiveQuery class for [[Shop]].
 *
 * @see Shop
 */
class ShopQuery extends \yii\db\ActiveQuery
{

	public function published()
	{
		return $this
			->active()
			->notShow()
			->andWhere('[[shop_id]] > 0');
	}

	/**
	 * @return $this
	 */
	public function active()
	{
		return $this->andWhere(['[[is_active]]' => Shop::IS_ACTIVE]);
	}

	/**
	 * @return $this
	 */
	public function notShow()
	{
		return $this->andWhere('[[not_show]] != :notShow', [
			':notShow' => Shop::NOT_SHOW,
		]);
	}

	/**
	 * @param RegionEntityInterface $region
	 * @return $this
	 */
	public function byRegion(RegionEntityInterface $region)
	{
		return $this->andWhere(['[[region_id]]' => $region->getRegionIdForShops()]);
	}

	/**
	 * @param int $regionId
	 * @return $this
	 */
	public function byRegionId(int $regionId)
	{
		return $this->andWhere(['[[region_id]]' => $regionId]);
	}

	/**
	 * @param int $zoneId
	 * @return $this
	 */
	public function byZoneId(int $zoneId): self
	{
		return $this->andWhere(['[[zone_id]]' => $zoneId]);
	}

	/**
	 * @param array|int $id
	 * @return $this
	 */
	public function byId($id)
	{
		return $this->andWhere(['[[shop_id]]' => $id]);
	}

	/**
	 * @param array|string $slug
	 * @return $this
	 */
	public function bySlug($slug)
	{
		return $this->andWhere(['[[url]]' => $slug]);
	}

	public function byShopIdIsSet()
	{
		return $this->andWhere('[[shop_id]] > 0');
	}

	public function byGroupIdIsSet()
	{
		return $this->andWhere('[[shopgroup_id]] > 0');
	}

	public function getShopAndGroup()
	{

		return $this
			->active()
			->byShopIdIsSet()
			->byGroupIdIsSet()
			->select([
				'group_id' => 'shopgroup_id',
				'shop_id',
				'zone_id',
				'title' => 'short_name',
			])
			->orderBy([
				'shopgroup_id' => SORT_ASC,
				'shop_id' => SORT_ASC,
			])
			->asArray();
	}

	public function defaultOrder()
	{
		return $this->orderBy([
			'ord_num' => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return Shop[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Shop|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
