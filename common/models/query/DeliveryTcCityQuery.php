<?php

namespace common\models\query;

use common\models\DeliveryTcCity;

/**
 * This is the ActiveQuery class for [[DeliveryTcCity]].
 *
 * @see DeliveryTcCity
 */
class DeliveryTcCityQuery extends \yii\db\ActiveQuery
{

	public function defaultOrder()
	{
		return $this->addOrderBy(['city' => SORT_ASC]);
	}

	/**
	 * @inheritdoc
	 * @return DeliveryTcCity[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return DeliveryTcCity|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
