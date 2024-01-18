<?php

namespace common\models\query;

use common\models\OptUserAddress;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[OptUserAddress]].
 *
 * @see OptUserAddress
 */
class OptUserAddressQuery extends ActiveQuery
{

	/**
	 * @return OptUserAddressQuery
	 */
	public function byUseInApi(): OptUserAddressQuery
	{
		//return $this->andWhere(['[[use_in_api]]' => OptUserAddress::USE_IN_API]);
		return $this;
	}

	/**
	 * @param $type string|string[]
	 * @return OptUserAddressQuery
	 */
	public function byDeliveryType($type): OptUserAddressQuery
	{
		return $this->andWhere([
			"{$this->getAlias()}.[[type]]" => $type,
		]);
	}

	/**
	 * @param $optUserId int|int[]
	 * @return OptUserAddressQuery
	 */
	public function byOptUserId($optUserId): OptUserAddressQuery
	{
		return $this->andWhere([
			"{$this->getAlias()}.[[opt_user_id]]" => $optUserId,
		]);
	}

	/**
	 * @param $hash string|string[]
	 * @return OptUserAddressQuery
	 */
	public function byHash($hash): OptUserAddressQuery
	{
		return $this->andWhere([
			"{$this->getAlias()}.[[hash]]" => $hash,
		]);
	}

	/**
	 * @param $id int|int[]
	 * @return OptUserAddressQuery
	 */
	public function byId($id): OptUserAddressQuery
	{
		return $this->andWhere([
			"{$this->getAlias()}.[[id]]" => $id,
		]);
	}

	public function orderDefault()
	{
		return $this->addOrderBy([
			"{$this->getAlias()}.[[updated_at]]" => SORT_DESC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return OptUserAddress[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return OptUserAddress|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

}
