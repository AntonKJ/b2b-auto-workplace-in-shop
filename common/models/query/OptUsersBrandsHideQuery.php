<?php

namespace common\models\query;

use yii\web\IdentityInterface;
use yii\web\User;

/**
 * This is the ActiveQuery class for [[\common\models\OptUserBrandHide]].
 *
 * @see \common\models\OptUserBrandHide
 */
class OptUsersBrandsHideQuery extends \yii\db\ActiveQuery
{

	public function findByType($type)
	{

		return $this->andWhere([
			'[[type]]' => $type,
		]);
	}

	/**
	 * @param IdentityInterface $user
	 * @return $this
	 */
	public function findByUser(User $user)
	{
		return $this->findByUserId($user->getId());
	}

	/**
	 * @param int $userId
	 * @return $this
	 */
	public function findByUserId($userId)
	{
		return $this->andWhere([
			'[[user_id]]' => $userId,
		]);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OptUserBrandHide[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\OptUserBrandHide|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
