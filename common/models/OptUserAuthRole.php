<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "opt_users_roles".
 *
 * @property integer $user_id
 * @property string $role
 * @property integer $created_at
 *
 * @property OptUser $user
 */
class OptUserAuthRole extends \yii\db\ActiveRecord
{

	const ROLE_GUEST = 'guest';
	const ROLE_USER = 'user';
	const ROLE_DEVELOPER = 'developer';

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'value' => function ($e) {
					return date('Y-m-d H:i:s');
				},
				'updatedAtAttribute' => false,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%opt_users_roles}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [

		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(OptUser::className(), ['id' => 'user_id']);
	}

	public static function getRoleOptions()
	{
		return [
			self::ROLE_GUEST => 'Гость',
			self::ROLE_USER => 'Зарегистрированный пользователь',
			self::ROLE_DEVELOPER => 'Разработчик',
		];
	}

	public function getRoleText()
	{

		$options = self::getRoleOptions();

		return isset($options[$this->role]) ? $options[$this->role] : 'Неизвестная роль';
	}
}
