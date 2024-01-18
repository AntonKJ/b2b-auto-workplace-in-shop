<?php

namespace common\models\query;

use common\models\Region;

/**
 * This is the ActiveQuery class for [[Region]].
 *
 * @see Region
 */
class RegionQuery extends \yii\db\ActiveQuery
{

	public function active()
	{
		return $this;
	}

	/**
	 * @param string $url
	 * @return $this
	 * @deprecated use instead bySlug
	 */
	public function byUrl($url)
	{
		return $this->bySlug($url);
	}

	/**
	 * @param string|array $slug
	 * @return $this
	 */
	public function bySlug($slug)
	{
		return $this->andWhere([
			'[[url_frag]]' => $slug,
		]);
	}

	/**
	 * @param int|array $id
	 * @return $this
	 */
	public function byId($id)
	{
		return $this->andWhere([
			'[[region_id]]' => $id,
		]);
	}

	/**
	 * @param int|array $zoneId
	 * @return $this
	 */
	public function byZoneId($zoneId)
	{
		return $this->andWhere([
			'[[zone_id]]' => $zoneId,
		]);
	}

	/**
	 * @param string|array $type
	 * @return $this
	 */
	public function byZoneType($type)
	{
		return $this->andWhere([
			'[[zone_type]]' => $type,
		]);
	}

	/**
	 * @param int|array $deliveryTypeId
	 * @return $this
	 */
	public function byDeliveryTypeId($deliveryTypeId)
	{
		return $this->andWhere([
			'[[region_deliverytype_id]]' => $deliveryTypeId,
		]);
	}

	/**
	 * @param bool $status
	 * @return $this
	 */
	public function isShowOnMenu($status = true)
	{
		if ((bool)$status)
			$this->andWhere(['is_show_on_menu' => Region::IS_SHOW_ON_MENU]);
		else
			$this->andWhere('is_show_on_menu IS NULL OR is_show_on_menu=0');

		return $this;
	}

	/**
	 * @param bool $status
	 * @return $this
	 */
	public function isActive($status = true)
	{
		return $this->andWhere(['is_active' => (bool)$status ? Region::IS_ACTIVE : 0]);
	}

	/**
	 * @return $this
	 */
	public function defaultOrder()
	{
		return $this->orderBy([
			'[[name]]' => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return Region[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Region|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
