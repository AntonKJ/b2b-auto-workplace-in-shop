<?php

namespace common\models\sphinx;

use common\models\DiskBrand;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class BrandDiskSearch extends DiskBrand
{

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function formName()
	{
		return '';
	}

	public function getSearchQuery($params)
	{

		$query = (new GoodDiskSearch())->getSearchQuery($params);

		$query
			->groupBy(['brand_id'])
			->asArray()
			->limit(1000)
			->orderBy([
				'brand_sortorder' => SORT_ASC,
				'brand_title' => SORT_ASC,
			]);

		return $query;
	}

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{

		$goodQuery = $this->getSearchQuery($params);

		$brandIds = [];
		foreach ($goodQuery->each() as $itm)
			$brandIds[] = (int)$itm['brand_id'];

		$query = static::find()
			->byId($brandIds)
			->orderBy(new Expression('FIELD(d_producer_id,' . implode(',', $brandIds) . ')'));

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => false,
			'pagination' => false,
		]);

		return $dataProvider;
	}
}
