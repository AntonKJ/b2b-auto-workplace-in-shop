<?php

namespace common\components;

use common\models\OptUser;
use Throwable;
use yii\db\StaleObjectException;

class User extends \yii\web\User
{

	public function loginByAccessToken($token, $type = null)
	{
		/** @var OptUser $identity */
		$identity = parent::loginByAccessToken($token, $type);
		if ($identity !== null) {
			$identity->setLoginByToken($token);
		}
		return $identity;
	}

	/**
	 * @param bool $destroySession
	 * @return bool
	 * @throws Throwable
	 * @throws StaleObjectException
	 */
	public function logout($destroySession = true)
	{
		/** @var OptUser $user */
		$user = $this->getIdentity();
		if ($user->isLoginByToken() && null !== ($token = $user->getLoginByToken())) {
			$tokenModel = $user->getAuthToken()
				->byCode($token)
				->one();
			if (null !== $tokenModel) {
				$tokenModel->delete();
			}
		}

		return parent::logout($destroySession);
	}

}
