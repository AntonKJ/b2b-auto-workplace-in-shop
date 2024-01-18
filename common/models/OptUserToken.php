<?php

namespace common\models;

use common\models\query\OptUserTokenQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Token Active Record model.
 *
 * @property integer $user_id
 * @property string $code
 * @property integer $created_at
 * @property integer $type
 * @property string $url
 * @property bool $isExpired
 * @property OptUser $user
 * @property int $id [int(11)]
 *
 */
class OptUserToken extends ActiveRecord
{

	const TYPE_AUTH = 'auth';
	const TYPE_RESET = 'reset';
	const TYPE_API = 'api';

	protected $resetWithin = 3600;
	protected $expirationTime = 2592000; // 3600 * 24 * 30

	/**
	 * @inheritdoc
	 * @return OptUserTokenQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new OptUserTokenQuery(static::class);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(OptUser::class, ['id' => 'user_id'])
			->inverseOf('authToken');
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{

		switch ($this->type) {

			case self::TYPE_RESET:

				$route = '/user/recoveryReset';
				break;

			default:
				throw new \RuntimeException('Current token type not have url.');
		}

		return Url::to([$route, 'id' => $this->user_id, 'code' => $this->code], true);
	}

	/**
	 * @return bool Whether token has expired.
	 * @throws \Exception
	 */
	public function getIsExpired()
	{

		switch ($this->type) {

			case self::TYPE_RESET:

				$expirationTime = $this->resetWithin;
				break;

			default:
				throw new \RuntimeException('Current token type not have expiration time.');
		}

		$dt = new \DateTime($this->created_at);
		return ($dt->getTimestamp() + $this->expirationTime) < time();
	}

	/** @inheritdoc
	 * @throws \yii\base\Exception
	 */
	public function beforeSave($insert)
	{

		if ($insert) {

			// Отключаем логин, только, с одного устройства
			//static::deleteAll(['user_id' => $this->user_id, 'type' => $this->type]);

			//todo разобраться с очисткой протухших токенов (не обновляется дата последнего логина по токену)
			//static::deleteOutdatedAuthTokens();
			$this->setAttribute('code', Yii::$app->security->generateRandomString());
		}

		$this->setAttribute('created_at', date('Y-m-d H:i:s'));

		return parent::beforeSave($insert);
	}

	public static function deleteOutdatedAuthTokens()
	{
		return static::deleteAll('[[type]]=:type AND [[created_at]]<:created_at', [
			':type' => static::TYPE_AUTH,
			':created_at' => date('Y-m-d H:i:s'),
		]);
	}

	/** @inheritdoc */
	public static function tableName()
	{
		return '{{%opt_users_token}}';
	}

}