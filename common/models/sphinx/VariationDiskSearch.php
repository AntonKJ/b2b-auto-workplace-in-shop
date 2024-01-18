<?php

namespace common\models\sphinx;

use common\models\DiskVariation;
use common\models\query\DiskModelQuery;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class VariationDiskSearch extends DiskVariation
{

	public $brand;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['brand'], 'safe'],
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
			->addSelect(new Expression('variation_params.id AS variation_id'))
			->addSelect(new Expression('variation_params.sortorder AS variation_sortorder'))
			->groupBy(['variation_id'])
			->asArray()
			->orderBy([
				'brand_sortorder' => SORT_ASC,
				'brand_title' => SORT_ASC,
				'model_sortorder' => SORT_ASC,
				'model_title' => SORT_ASC,
				'variation_sortorder' => SORT_ASC,
			])
			->limit(100000);

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

		$variationIds = [];
		foreach ($goodQuery->each() as $itm)
			$variationIds[] = (int)$itm['variation_id'];

		$query = parent::find();

		$query
			->byId($variationIds)
			->orderBy(new Expression('FIELD([[id]],' . implode(',', $variationIds) . ')'))
			->with([
				'model' => function (DiskModelQuery $q) {
					$q->with(['brand']);
				},
				'color',
			]);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => false,
			'pagination' => false,
		]);

		return $dataProvider;
	}
}
