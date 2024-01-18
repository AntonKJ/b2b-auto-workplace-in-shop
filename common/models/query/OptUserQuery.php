<?php

namespace common\models\query;

use common\models\OptUser;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[OptUser]].
 *
 * @see OptUser
 */
class OptUserQuery extends ActiveQuery
{
	/**
	 * @param string[]|string $email
	 * @return OptUserQuery
	 */
	public function byEmail($email): OptUserQuery
	{
		return $this->andWhere([
			'[[email]]' => $email,
		]);
	}

	/**
	 * @return OptUserQuery
	 */
	public function active()
	{
		return $this->andWhere([
			'[[is_active]]' => OptUser::IS_ACTIVE,
		]);
	}

	/**
	 * @return OptUserQuery
	 */
	public function byApiIsActive(): OptUserQuery
	{
		return $this->andWhere([
			'[[is_api_active]]' => OptUser::API_IS_ACTIVE,
		]);
	}

	/**
	 * @inheritdoc
	 * @return OptUser[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return OptUser|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
