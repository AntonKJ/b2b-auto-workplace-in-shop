<?php

namespace common\models\search;

use common\models\DiskBrand;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class DiskBrandSearch extends DiskBrand
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


		$goodQuery = (new DiskSearch())->getSearchQuery($params);

		$query = parent::find();

		$query
			->alias('b')
			->select(['b.*'])
			->distinct(true)
			->innerJoin(['g' => $goodQuery], 'g.brand_id = b.d_producer_id')
			->published()
		;

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => [
					'default' => SORT_ASC,
				],
				'attributes' => [
					'default' => [
						'asc' => [
							'b.pos' => SORT_ASC,
							'b.name' => SORT_ASC,
						],
						'desc' => [
							'b.pos' => SORT_DESC,
							'b.name' => SORT_DESC,
						],
						'default' => SORT_ASC,
					],
				],
			],
			'pagination' => false,
		]);

		return $dataProvider;
	}
}
