<?php

namespace common\models\sphinx;

use common\models\TyreModel;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class ModelTyreSearch extends TyreModel
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

		$query = (new GoodTyreSearch())->getSearchQuery($params);

		$query
			->groupBy(['model_id'])
			->asArray()
			->limit(100000)
			->orderBy([
				'brand_sortorder' => SORT_ASC,
				'brand_title' => SORT_ASC,
				'model_sortorder' => SORT_ASC,
				'model_title' => SORT_ASC,
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

		$modelsIds = [];
		foreach ($goodQuery->each() as $itm)
			$modelsIds[] = (int)$itm['model_id'];

		$query = parent::find();

		$query
			->byId($modelsIds)
			->orderBy(new Expression('FIELD(id,' . implode(',', $modelsIds) . ')'));

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => false,
			'pagination' => false,
		]);

		return $dataProvider;
	}
}
