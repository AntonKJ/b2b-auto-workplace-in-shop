<?php

namespace common\models\query;

use common\interfaces\RegionEntityInterface;
use yii\db\ActiveQuery;
use yii\web\IdentityInterface;

/**
 * This is the ActiveQuery class for [[\common\models\ShoppingCart]].
 *
 * @see \common\models\ShoppingCart
 */
class ShoppingCartQuery extends \yii\db\ActiveQuery
{
	/**
	 * @param RegionEntityInterface $region
	 * @return $this
	 */
	public function byRegion(RegionEntityInterface $region)
	{
		return $this->andWhere(['region_id' => $region->getId()]);
	}

	/**
	 * @param IdentityInterface $user
	 * @return $this
	 */
	public function byUser(IdentityInterface $user)
	{
		return $this->andWhere(['user_id' => $user->getId()]);
	}

	/**
	 * @param RegionEntityInterface $user
	 * @return $this
	 */
	public function byToken(string $token)
	{
		return $this
			->innerJoinWith(['token' => function (ActiveQuery $q) use ($token) {
				$q->byToken($token);
			}], false);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShoppingCart[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\ShoppingCart|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
