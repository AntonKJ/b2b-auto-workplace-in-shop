<?php

namespace common\models\query;

use common\models\TyreModel;
use yii\base\InvalidCallException;

/**
 * This is the ActiveQuery class for [[TyreModel]].
 *
 * @see TyreModel
 */
class TyreModelQuery extends \yii\db\ActiveQuery
{

	public function active(): TyreModelQuery
	{
		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[is_published]]" => TyreModel::IS_PUBLISHED,
		]);
	}

	public function byUrl($url): TyreModelQuery
	{

		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[url]]" => $url,
		]);
	}

	public function byId($id): TyreModelQuery
	{

		$alias = $this->getAlias();
		return $this->andWhere([
			"{$alias}.[[id]]" => $id,
		]);
	}

	public function byBrandUrl($brandUrl): TyreModelQuery
	{
		throw new InvalidCallException();
	}

	/**
	 * @return $this
	 */
	public function defaultOrder()
	{
		$alias = $this->getAlias();
		return $this->orderBy([
			"{$alias}.[[Position]]" => SORT_ASC,
			"{$alias}.[[name]]" => SORT_ASC,
		]);
	}

	/**
	 * @inheritdoc
	 * @return TyreModel[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return TyreModel|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
