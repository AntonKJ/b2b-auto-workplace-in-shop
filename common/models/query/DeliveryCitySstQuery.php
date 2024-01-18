<?php

namespace common\models\query;

use common\models\DeliveryCitySst;

/**
 * This is the ActiveQuery class for [[DeliveryCitySst]].
 *
 * @see DeliveryCitySst
 */
class DeliveryCitySstQuery extends \yii\db\ActiveQuery
{

	public function defaultOrder()
	{
		return $this->addOrderBy(['[[name]]' => SORT_ASC]);
	}

	public function byZoneId($zoneId)
	{
		return $this->andWhere(['[[zone_id]]' => $zoneId]);
	}

	public function byIsActive()
	{
		return $this->andWhere(['[[is_active]]' => DeliveryCitySst::IS_ACTIVE]);
	}

	/**
	 * @inheritdoc
	 * @return DeliveryCitySst[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return DeliveryCitySst|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
