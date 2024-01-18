<?php

namespace api\modules\regular\models;

use common\models\OptUserToken;
use common\models\query\OptUserQuery;
use yii\db\ActiveQuery;

class ApiUserToken extends OptUserToken
{

	/**
	 * @return OptUserQuery|ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(ApiUser::class, ['id' => 'user_id'])
			->inverseOf('apiToken');
	}

}