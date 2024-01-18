<?php

namespace common\models\search;

use common\models\TyreBrand;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class TyreBrandSearch extends TyreBrand
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

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{

		$goodQuery = (new TyreSearch())->getSearchQuery($params);

		$query = parent::find();

		$query
			->alias('b')
			->select(['b.*'])
			->distinct(true)
			->innerJoin(['g' => $goodQuery], 'g.prod_code = b.code')
			->defaultOrder()
		;

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => false,
			'pagination' => false,
		]);

		return $dataProvider;
	}
}
