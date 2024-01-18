<?php

namespace common\components;

use common\models\OptUser;
use Yii;
use yii\base\Request;
use yii\log\FileTarget;
use yii\web\Session;
use function call_user_func;

class LoggerFileTarget extends FileTarget
{

	/**
	 * @inheritdoc
	 */
	public function getMessagePrefix($message)
	{
		if ($this->prefix !== null) {
			return call_user_func($this->prefix, $message);
		}

		if (Yii::$app === null) {
			return '';
		}

		$request = Yii::$app->getRequest();
		$ip = $request instanceof Request ? $request->getUserIP() : '-';

		/* @var $user \yii\web\User */
		$user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;

		$userID = '-';
		$userEmail = '-';

		/** @var OptUser $identity */
		if ($user && ($identity = $user->getIdentity(false))) {

			$userID = $identity->getId();

			if ($identity instanceof OptUser)
				$userEmail = $identity->getEmail();
		}

		/* @var $session Session */
		$session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
		$sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

		return "[$ip][$userID][$userEmail][$sessionID]";
	}

}
