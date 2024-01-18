<?php

namespace common\models\search;

use common\models\AutoBrand;
use yii\base\Model;

class AutoBrandSearch extends AutoBrand
{

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['q'], 'safe'],
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

		$query = parent::find();

		$query
			->alias('ab')
			->select(['ab.*'])
		;

		return $query;
	}
}
