<?php

namespace cp\models;

use common\models\OptUserProducerRestrict;
use common\models\TyreBrand;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class OptUser extends \common\models\OptUser
{

	public $disk_brand_restrict;
	public $tyre_brand_restrict;

	public function rules()
	{
		return [
			[['disk_brand_restrict', 'tyre_brand_restrict'], 'safe'],
			[['region_id', 'ou_category_id'], 'safe'],
			[['email', 'fullname', 'code_1c'], 'string', 'max' => 255],
		];
	}

	public function getTyreBrandRestrictRel()
	{
		return $this->hasMany(OptUserProducerRestrict::class, ['opt_user_id' => 'id']);
	}

	public function getTyreBrandRestrict()
	{
		return $this->hasMany(TyreBrand::class, ['id' => 'producer_id'])
			->viaTable(OptUserProducerRestrict::tableName(), ['opt_user_id' => 'id']);
	}

	public function search($params)
	{
		$query = static::find();

		$query->with([
			'category',
			'region',
			'tyreBrandRestrict',
		]);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 100,
			],
		]);

		// load the search form data and validate
		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		if ((int)$this->tyre_brand_restrict > 0) {
			$query->joinWith([
				'tyreBrandRestrictRel' => static function (ActiveQuery $q) {
					$q->alias('tbr');
				},
			], false);
			$query
				->andFilterWhere([
					//'p.id' => $this->disk_brand_restrict,
					'tbr.producer_id' => $this->tyre_brand_restrict,
				]);
		}

		$query
			->andFilterWhere([
				'region_id' => $this->region_id,
				'ou_category_id' => $this->ou_category_id,
			])
			->andFilterWhere(['like', 'code_1c', $this->code_1c])
			->andFilterWhere(['like', 'email', $this->email])
			->andFilterWhere(['like', 'fullname', $this->fullname]);

		return $dataProvider;
	}
}
