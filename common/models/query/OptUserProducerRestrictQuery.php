<?php

namespace common\models\query;

use yii\web\IdentityInterface;
use yii\web\User;

class OptUserProducerRestrictQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param IdentityInterface $user
	 * @return $this
	 */
	public function byUser(User $user): self
	{
		return $this->byUserId($user->getId());
	}

	/**
	 * @param int $userId
	 * @return $this
	 */
	public function byUserId(int $userId): self
	{
		return $this->andWhere([
			'[[opt_user_id]]' => $userId,
		]);
	}

	/**
	 * @param int $brandId
	 * @return $this
	 */
	public function byBrandId(int $brandId): self
	{
		return $this->andWhere([
			'[[producer_id]]' => $brandId,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OptUserProducerRestrict[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OptUserProducerRestrict|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

}