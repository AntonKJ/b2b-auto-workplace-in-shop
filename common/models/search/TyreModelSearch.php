<?php

namespace common\models\search;

use common\models\TyreModel;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class TyreModelSearch extends TyreModel
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
			->alias('m')
			->select(['m.*'])
			->distinct(true)
			->innerJoin(['g' => $goodQuery], 'g.prod_code = m.prod_code AND g.p_t = m.code')
			->defaultOrder()
			->active()
		;

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => false,
		]);

		return $dataProvider;
	}
}
