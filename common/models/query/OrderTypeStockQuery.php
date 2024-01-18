<?php

namespace common\models\query;

use common\interfaces\OrderTypeGroupableInterface;

/**
 * This is the ActiveQuery class for [[\common\models\OrderTypeStock]].
 *
 * @see \common\models\OrderTypeStock
 */
class OrderTypeStockQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param int[] $ids
	 * @return $this
	 */
	public function byGoodId($ids)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[item_idx]]" => $ids,
		]);
	}

	/**
	 * @param int[] $ids
	 * @return $this
	 */
	public function byOrderTypeId($ids)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[order_type_id]]" => $ids,
		]);
	}

	/**
	 * @param OrderTypeGroupableInterface $orderTypeGroupable
	 * @return OrderTypeStockQuery
	 */
	public function byOrderTypeGroup(OrderTypeGroupableInterface $orderTypeGroupable)
	{
		return $this->byOrderTypeGroupId($orderTypeGroupable->getOrderTypeGroupId());
	}

	/**
	 * @param int|array $id
	 * @return $this
	 */
	public function byOrderTypeGroupId($id)
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[order_type_group_id]]" => $id,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OrderTypeStock[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OrderTypeStock|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
