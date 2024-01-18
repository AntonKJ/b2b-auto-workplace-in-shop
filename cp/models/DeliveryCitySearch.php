<?php

namespace cp\models;

use common\models\DeliveryCity;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class DeliveryCitySearch extends DeliveryCity
{

	public $zone_id;
	public $zonesCount;

	public function rules()
	{
		return [
			[['id', 'zone_id', 'City', 'delivery_days', 'lat', 'lng', 'delivery_area_radius'], 'safe'],
			[['City'], 'string', 'max' => 255],
		];
	}

	/**
	 * @param $params
	 * @return ActiveDataProvider
	 */
	public function search($params): ActiveDataProvider
	{
		$query = static::find();

		$query->distinct();

		$query->innerJoinWith([
			'zones',
			'zonesRel' => static function (ActiveQuery $q) {
				$q->alias('dzr');
			},
		]);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		// load the search form data and validate
		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$query->andFilterWhere(['dzr.delivery_zone_id' => $this->zone_id]);

		$query->andFilterWhere(['id' => $this->id]);
		$query->andFilterWhere(['delivery_days' => $this->delivery_days]);
		$query->andFilterWhere(['lat' => $this->lat]);
		$query->andFilterWhere(['lng' => $this->lng]);
		$query->andFilterWhere(['delivery_area_radius' => $this->delivery_area_radius]);
		$query
			->andFilterWhere(['like', 'City', $this->City]);

		return $dataProvider;
	}

}
