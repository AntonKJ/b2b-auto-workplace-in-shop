<?php

namespace common\models;

use common\components\auth\AuthRoleModelInterface;
use common\interfaces\B2BUserInterface;
use common\interfaces\RegionEntityInterface;
use common\models\query\OptUserAddressQuery;
use common\models\query\OptUserQuery;
use common\models\query\OptUserTokenQuery;
use common\models\query\RegionQuery;
use domain\interfaces\PaymentTypesInterface;
use domain\traits\PaymentTypesTrait;
use InvalidArgumentException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "opt_users".
 *
 * @property integer $id
 * @property string $email
 * @property string $pass
 * @property integer $price_category
 * @property integer $last_visit_tmstmp
 * @property string $code_1c
 * @property string $name
 * @property string $fullname
 * @property integer $ou_category_id
 * @property integer $has_credit
 * @property string $bonus
 * @property string $credit
 * @property integer $region_id
 * @property string $balance
 * @property integer $is_active
 * @property string $ip
 * @property integer $num_visits
 * @property string $ip2
 *
 * @property RegionEntityInterface $region
 */
class OptUser extends ActiveRecord implements IdentityInterface, AuthRoleModelInterface, B2BUserInterface
{

	use PaymentTypesTrait;

	public const ROLE_USER = 'user';

	public const IS_ACTIVE = 1;
	public const HAS_CREDIT = 1;

	public const API_IS_ACTIVE = 1;

	private $_loginByToken;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%opt_users}}';
	}

	/**
	 * Finds an identity by the given ID.
	 * @param string|int $id the ID to be looked for
	 * @return IdentityInterface the identity object that matches the given ID.
	 * Null should be returned if such an identity cannot be found
	 * or the identity is not in an active state (disabled, deleted, etc.)
	 */
	public static function findIdentity($id)
	{
		return static::find()
			->active()
			->andWhere(['id' => $id])
			->one();
	}

	/**
	 * @inheritdoc
	 * @return OptUserQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new OptUserQuery(static::class);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByEmail($email)
	{
		return static::find()
			->active()
			->andWhere(['email' => $email])
			->one();
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByEmailAndPassword($email, $password)
	{
		return static::find()
			->active()
			->andWhere([
				'email' => $email,
				'pass' => $password,
			])
			->one();
	}

	/**
	 * Finds an identity by the given token.
	 * @param mixed $token the token to be looked for
	 * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
	 * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
	 * @return IdentityInterface the identity object that matches the given token.
	 * Null should be returned if such an identity cannot be found
	 * or the identity is not in an active state (disabled, deleted, etc.)
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{

		$tokenModel = OptUserToken::find()
			->typeAuth()
			->andWhere(['code' => $token])
			->innerJoinWith(['user' => function (OptUserQuery $q) {
				$q->active();
			}])
			->one();

		return null !== $tokenModel ? $tokenModel->user : null;
	}

	/**
	 * @param mixed $id
	 * @return AuthRoleModelInterface
	 */
	public static function findAuthRoleIdentity($id)
	{
		return static::findOne($id);
	}

	/**
	 * @param string $roleName
	 * @return array
	 */
	public static function findAuthIdsByRoleName($roleName)
	{
		return OptUserAuthRole::find()
			->select('user_id')
			->where(['role' => $roleName])
			->column();
	}

	/**
	 * @inheritdoc
	 */
	public function getOrderTypeGroupId()
	{
		return $this->category === null ? null : (int)$this->category->order_type_group_id;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['email', 'pass', 'price_category'], 'required'],
			[['price_category', 'last_visit_tmstmp', 'ou_category_id', 'has_credit', 'region_id', 'is_active', 'num_visits'], 'integer'],
			[['email'], 'string', 'max' => 100],
			[['pass', 'name'], 'string', 'max' => 50],
			[['code_1c', 'bonus', 'credit', 'balance', 'ip', 'ip2'], 'string', 'max' => 31],
			[['fullname'], 'string', 'max' => 150],
			[['email'], 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'email' => 'Адрес эл. почты',
			'pass' => 'Пароль',
		];
	}

	/**
	 * @return ActiveQuery|OptUserTokenQuery
	 */
	public function getAuthToken()
	{
		return $this->hasOne(OptUserToken::class, ['user_id' => 'id'])
			->andOnCondition(['[[type]]' => OptUserToken::TYPE_AUTH])
			->inverseOf('user');
	}

	/**
	 * @return ActiveQuery|OptUserTokenQuery
	 */
	public function getApiToken()
	{
		return $this->hasOne(OptUserToken::class, ['user_id' => 'id'])
			->andOnCondition(['[[type]]' => OptUserToken::TYPE_API])
			->inverseOf('user');
	}

	/**
	 * @return ActiveQuery|RegionQuery
	 */
	public function getRegion()
	{
		return $this->hasOne(Region::class, ['region_id' => 'region_id']);
	}

	/**
	 * @return ActiveQuery|OptUserAddressQuery
	 */
	public function getAddresses()
	{
		return $this->hasMany(OptUserAddress::class, ['opt_user_id' => 'id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getCategory()
	{
		return $this->hasOne(OptUserCategory::class, ['ou_category_id' => 'ou_category_id'])
			->inverseOf('user');
	}

	/**
	 * @return int
	 */
	public function getCategoryId(): int
	{
		return $this->ou_category_id;
	}

	/**
	 * @param int $categoryId
	 */
	public function setCategoryId(int $categoryId)
	{
		$this->ou_category_id = $categoryId;
	}

	/**
	 * Returns an ID that can uniquely identify a user identity.
	 * @return string|int an ID that uniquely identifies a user identity.
	 */
	public function getId()
	{
		return $this->getPrimaryKey();
	}

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return trim($this->email);
	}

	/**
	 * @return string
	 */
	public function getClientCode()
	{
		return $this->code_1c;
	}

	/**
	 * @return string
	 */
	public function getRegionId()
	{
		return (int)$this->region_id;
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return boolean if password provided is valid for current user
	 */
	public function validatePassword($password)
	{

		if (!is_string($password) || $password === '')
			throw new InvalidArgumentException('Password must be a string and cannot be empty.');

		return $this->pass === $password;
	}

	/**
	 * Validates the given auth key.
	 *
	 * This is required if [[User::enableAutoLogin]] is enabled.
	 * @param string $authKey the given auth key
	 * @return bool whether the given auth key is valid.
	 * @see getAuthKey()
	 */
	public function validateAuthKey($authKey)
	{
		return !empty($authKey) && $this->getAuthKey() === $authKey;
	}

	/**
	 * Returns a key that can be used to check the validity of a given identity ID.
	 *
	 * The key should be unique for each individual user, and should be persistent
	 * so that it can be used to check the validity of the user identity.
	 *
	 * The space of such keys should be big enough to defeat potential identity attacks.
	 *
	 * This is required if [[User::enableAutoLogin]] is enabled.
	 * @return string a key that is used to check the validity of a given identity ID.
	 * @see validateAuthKey()
	 */
	public function getAuthKey()
	{
		return $this->authToken !== null ? $this->authToken->code : null;
	}

	public function getApiKey()
	{
		return $this->apiToken !== null ? $this->apiToken->code : null;
	}

	/**
	 * Обновляем токен
	 */
	public function updateAccessToken()
	{

		$token = new OptUserToken;

		$token->user_id = $this->getPrimaryKey();
		$token->type = OptUserToken::TYPE_AUTH;

		$token->save(false);

		$this->populateRelation('authToken', $token);

		$this->updateAccessInfo();

	}

	/**
	 * Обновляем информацию о последнем визите
	 */
	public function updateAccessInfo()
	{

		$this->num_visits = (int)$this->num_visits + 1;

		$this->last_visit_tmstmp = time();

		if (($ip = Yii::$app->request->userIP) != '' && $ip != $this->ip) {

			$this->ip2 = $this->ip;
			$this->ip = Yii::$app->request->userIP;
		}

		$this->updateAttributes(['last_visit_tmstmp', 'num_visits', 'ip', 'ip2']);

	}

	/**
	 * @return array
	 */
	public function getAuthRoleNames()
	{
		return ['user'];
//		return $this->getDb()->cache(function ($db) {
//			return $this->getRoles()->select('role')->column();
//		});
	}

	/**
	 * @return ActiveQuery
	 */
	public function getRoles()
	{
		return $this->hasMany(OptUserAuthRole::class, ['user_id' => 'id']);
	}

	/**
	 * @param string $roleName
	 */
	public function addAuthRoleName($roleName)
	{
		$role = $this->getRoles()
			->andWhere(['role' => $roleName])
			->one();

		if (null === $role) {

			$role = new OptUserAuthRole;
			$role->role = $roleName;

			$role->link('user', $this);
		}
	}

	/**
	 * @param string $roleName
	 */
	public function removeAuthRoleName($roleName)
	{
		OptUserAuthRole::deleteAll([
			'user_id' => $this->getPrimaryKey(),
			'role' => $roleName,
		]);
	}

	/**
	 * @return string
	 */
	public function getAvatarUrl()
	{
		return 'https://www.gravatar.com/avatar/' . md5(mb_strtolower($this->getEmail())) . '.png?s=80&d=robohash';
	}

	/**
	 * Removes all roles
	 */
	public function clearAuthRoleNames()
	{
		OptUserAuthRole::deleteAll(['user_id' => $this->getPrimaryKey()]);
	}

	public function fields()
	{

		$fields = [

			'id',

			'email',
			'apiKey',

			'name',
			'fullname',
			'avatarUrl',

			'isApiActive' => function (self $model) {
				return $model->apiIsActive();
			},

			'categoryId',
			'categoryText' => function (self $model) {
				return null !== $model->category ? $model->category->title : null;
			},

			'lastVisitAt' => 'last_visit_tmstmp',

			'clientCode',

			'creditAllowed' => function ($model) {
				return (int)$model->has_credit == static::HAS_CREDIT;
			},

			'credit' => function ($model) {
				return (float)$model->credit;
			},

			'bonus' => function ($model) {
				return (float)preg_replace('[^\d\.]', '', str_replace(',', '.', $model->bonus));
			},

			'balance' => function ($model) {
				return (float)$model->balance;
			},

		];

		return $fields;
	}

	/**
	 * @param string|null $loginByToken
	 * @return OptUser
	 */
	public function setLoginByToken(?string $loginByToken)
	{
		$this->_loginByToken = $loginByToken;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getLoginByToken()
	{
		return $this->_loginByToken;
	}

	public function isLoginByToken()
	{
		return null !== ($_t = $this->getLoginByToken()) && !empty($_t);
	}

	public function getPaymentTypeMask(): int
	{
		return $this->allowed_paytypes === null ? PaymentTypesInterface::TYPE_ALL : (int)$this->allowed_paytypes;
	}

	public function apiIsActive(): bool
	{
		return (int)$this->is_api_active === static::API_IS_ACTIVE;
	}

}
