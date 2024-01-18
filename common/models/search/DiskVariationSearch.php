<?php

namespace common\models\search;

use common\models\DiskVariation;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class DiskVariationSearch extends DiskVariation
{

	public $brand;

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
			->alias('v')
			->select(['v.*', 'm_sort' => 'm.sortorder', 'm_title' => 'm.title'])
			->distinct(true)
			->innerJoin(['g' => $goodQuery], 'g.variation_id = v.id')
			->published()
			->joinWith(['color', 'model' => function(ActiveQuery $q) {
				$q
					->alias('m')
					->joinWith(['brand b'])
				;
			}]) // оптимизация выборки ()
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
							'm.sortorder' => SORT_ASC,
							'm.title' => SORT_ASC,
							'v.sortorder' => SORT_ASC,
							'v.title' => SORT_ASC,
						],
						'desc' => [
							'm.sortorder' => SORT_DESC,
							'm.title' => SORT_DESC,
							'v.sortorder' => SORT_DESC,
							'v.title' => SORT_DESC,
						],
						'default' => SORT_ASC,
					],
				],
			],
		]);

		return $dataProvider;
	}
}
