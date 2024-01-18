<?php

namespace common\models\query;

use common\models\OptUserToken;

/**
 * This is the ActiveQuery class for [[OptUserToken]].
 *
 * @see OptUserToken
 */
class OptUserTokenQuery extends \yii\db\ActiveQuery
{

	public function typeAuth()
	{
		return $this->andWhere([
			'[[type]]' => OptUserToken::TYPE_AUTH,
		]);
	}

	public function typeReset()
	{
		return $this->andWhere([
			'[[type]]' => OptUserToken::TYPE_RESET,
		]);
	}

	public function typeApi()
	{
		return $this->andWhere([
			'[[type]]' => OptUserToken::TYPE_API,
		]);
	}

	public function byCode($code)
	{
		return $this->andWhere(['code' => $code]);
	}

	public function byUserId($userId)
	{
		return $this->andWhere(['user_id' => $userId]);
	}

	public function defaultOrder()
	{
		return $this->addOrderBy(['[[created_at]]' => SORT_DESC]);
	}

	/**
	 * @inheritdoc
	 * @return OptUserToken[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return OptUserToken|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
