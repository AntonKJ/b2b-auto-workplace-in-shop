<?php

namespace api\models;

use api\components\interfaces\VendorUserInterface;
use common\components\auth\AuthRoleModelInterface;
use common\interfaces\B2BUserInterface;
use common\models\OptUser;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

class VendorUser extends Model implements IdentityInterface, AuthRoleModelInterface, VendorUserInterface
{
	const STATUS_ACTIVE = 1;

	public $id;
	public $username;
	public $vendor;
	public $password;
	public $status;
	public $roles;
	public $authToken;
	public $optUserId;

	/**
	 * @var array
	 */
	public $orderData;

	protected $_users;

	/**
	 * @return mixed
	 */
	public static function getUserRepository()
	{

		static $users;

		if (null === $users)
			$users = require \Yii::getAlias('@api/config/vendor-users.php');

		return $users;
	}

	public function getVendor()
	{
		return $this->vendor;
	}

	public function getOptUserId(): ?int
	{
		return $this->optUserId;
	}

	public function getOptUser(): ?B2BUserInterface
	{

		if ($this->getOptUserId() === null)
			return null;

		static $cache = [];
		if (!isset($cache[$this->getOptUserId()])) {

			$cache[$this->getOptUserId()] = OptUser::findIdentity($this->getOptUserId());
		}

		return $cache[$this->getOptUserId()];
	}

	public static function findIdentity($id)
	{
		$users = static::getUserRepository();

		if (isset($users[(int)$id])) {

			$user = new self();
			$user->setAttributes($users[(int)$id], false);

			return $user;
		}

		return null;
	}

	protected static function indexByAuthToken()
	{

		static $users;

		if ($users === null)
			$users = ArrayHelper::index(static::getUserRepository(), 'authToken');

		return $users;
	}

	protected static function indexByUsername()
	{

		static $users;

		if ($users === null)
			$users = ArrayHelper::index(static::getUserRepository(), 'username');

		return $users;
	}

	protected static function indexByVendor()
	{

		static $users;

		if ($users === null)
			$users = ArrayHelper::index(static::getUserRepository(), 'vendor');

		return $users;
	}

	public static function findIdentityByAccessToken($token, $type = null)
	{
		$users = static::indexByAuthToken();

		if (isset($users[$token])) {

			$user = new self();
			$user->setAttributes($users[$token], false);

			return $user;
		}

		return null;
	}

	public static function findIdentityByUsername($name)
	{
		$users = static::indexByUsername();

		if (isset($users[$name])) {

			$user = new self();
			$user->setAttributes($users[$name], false);

			return $user;
		}

		return null;
	}

	public static function findIdentityByVendor($vendor)
	{
		$users = static::indexByVendor();

		if (isset($users[$vendor])) {

			$user = new self();
			$user->setAttributes($users[$vendor], false);

			return $user;
		}

		return null;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getAuthKey()
	{
		return $this->authToken;
	}

	public function validateAuthKey($authKey)
	{
		return !empty($authKey) && $this->getAuthKey() === $authKey;
	}

	public static function findAuthRoleIdentity($id)
	{
		return static::findIdentity($id);
	}

	/**
	 * @param string $roleName
	 * @return array
	 */
	public static function findAuthIdsByRoleName($roleName)
	{

		$users = static::getUserRepository();

		$ids = [];
		foreach ($users as $user)
			if (\in_array($roleName, $user['roles']))
				$ids[] = $user['id'];

		return $ids;
	}

	public function getAuthRoleNames()
	{
		return $this->roles;
	}

	public function addAuthRoleName($roleName)
	{
		if (!\in_array($roleName, $this->roles))
			$this->roles[] = $roleName;
	}

	public function removeAuthRoleName($roleName)
	{
		ArrayHelper::removeValue($this->roles, $roleName);
	}

	public function clearAuthRoleNames()
	{
		$this->roles = [];
	}


}
