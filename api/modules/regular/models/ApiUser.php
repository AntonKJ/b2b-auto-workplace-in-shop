<?php

namespace api\modules\regular\models;

use api\components\interfaces\VendorUserInterface;
use api\config\rbac\PermissionVendor;
use api\models\VendorUserRole;
use common\interfaces\B2BUserInterface;
use common\models\OptUser;
use common\models\query\OptUserQuery;

class ApiUser extends OptUser implements VendorUserInterface
{

	public static function findIdentityByAccessToken($token, $type = null)
	{

		$tokenModel = ApiUserToken::find()
			->typeApi()
			->andWhere(['code' => $token])
			->innerJoinWith(['user' => static function (OptUserQuery $q) {
				$q
					->active()
					->byApiIsActive();
			}])
			->one();

		return null !== $tokenModel ? $tokenModel->user : null;
	}

	/**
	 * @return array
	 */
	public function getAuthRoleNames()
	{
		return [
			VendorUserRole::ROLE_USER,
			PermissionVendor::REGULAR,
		];
	}

	public function getVendor()
	{
		return $this->getClientCode();
	}

	public function getOptUserId(): ?int
	{
		return $this->getId();
	}

	public function getOptUser(): ?B2BUserInterface
	{
		return $this;
	}

}
