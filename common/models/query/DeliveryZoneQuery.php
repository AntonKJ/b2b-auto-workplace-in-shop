<?php

namespace common\models\query;

use domain\entities\GeoPosition;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[\common\models\DeliveryZone]].
 *
 * @see \common\models\DeliveryZone
 */
class DeliveryZoneQuery extends \yii\db\ActiveQuery
{

	public function addSelectAreaAsJson()
	{

		$alias = $this->getAlias();

		if (!\is_array($this->select) || [] === $this->select)
			$this->addSelect(["{$alias}.*"]);

		return $this->addSelect([new Expression("ST_AsGeoJSON({$alias}.[[delivery_area]]) AS delivery_area")]);
	}

	public function byDeliveryAreaNotEmpty()
	{
		$alias = $this->getAlias();

		return $this
			->andWhere("{$alias}.[[delivery_area]] IS NOT NULL");
	}

	/**
	 * @param float $lat
	 * @param float $lng
	 */
	public function byCoords(float $lat, float $lng)
	{
		$alias = $this->getAlias();
		return $this
			->andWhere("{$alias}.[[delivery_area]] IS NOT NULL")
			->andWhere("ST_CONTAINS({$alias}.[[delivery_area]], POINT(:lat, :lng))", [
				':lat' => $lat,
				':lng' => $lng,
			]);
	}

	/**
	 * @param GeoPosition $position
	 */
	public function byGeoPosition(GeoPosition $position)
	{
		return $this
			->byCoords($position->getLat(), $position->getLng());
	}

	/**
	 * @param $id
	 * @return DeliveryZoneQuery
	 */
	public function byOrderTypeId($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[order_type_id]]" => $id,
		]);
	}

	/**
	 * @param $id
	 * @return DeliveryZoneQuery
	 */
	public function byId($id): DeliveryZoneQuery
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[id]]" => $id,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OrderType[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OrderType|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
