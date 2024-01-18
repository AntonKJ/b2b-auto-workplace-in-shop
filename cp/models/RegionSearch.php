<?php


namespace cp\models;

use common\models\Region;
use yii\data\ActiveDataProvider;

class RegionSearch extends Region
{
	/**
	 * @param int $regionId
	 * @return RegionSearch
	 */
	public function setId(int $regionId): RegionSearch
	{
		$this->region_id = $regionId;
		return $this;
	}

	public function rules()
	{
		return [
			[['region_id', 'zone_id', 'alt_zone_id', 'order_type_group_id'], 'safe'],
			[['name', 'url_frag'], 'string', 'max' => 255],
		];
	}

	public function search($params)
	{
		$query = static::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		// load the search form data and validate
		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$query->andFilterWhere(['region_id' => $this->region_id]);
		$query->andFilterWhere(['zone_id' => $this->zone_id]);
		$query->andFilterWhere(['alt_zone_id' => $this->alt_zone_id]);
		$query->andFilterWhere(['order_type_group_id' => $this->order_type_group_id]);
		$query
			->andFilterWhere(['like', 'name', $this->name])
			->andFilterWhere(['like', 'url_frag', $this->url_frag]);

		return $dataProvider;
	}

}
