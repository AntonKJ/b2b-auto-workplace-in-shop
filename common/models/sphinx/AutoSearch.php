<?php

namespace common\models\sphinx;

use common\components\SearchAbstract;
use yii\db\Expression;

class AutoSearch extends SearchAbstract
{

	public $q;
	public $year;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

			[['q'], 'required'],
			[['q'], 'string', 'max' => 255],

			[['year'], 'filter', 'filter' => [$this, 'prepareFilterYearParam']],
			[['year'], 'safe'],

		];
	}

	public function prepareFilterYearParam($value)
	{

		$value = trim($value);
		if ((int)$value == 0 && !$this->isEmpty($this->q)) {

			$year = $this->prepareYears($this->q);
			if ($year !== false && (int)$year > 0)
				$value = $year;
		}

		return $value;
	}

	protected function prepareYears($query)
	{

		preg_match_all('/ \b (?P<year> 19\d{2} | 20\d{2} ) \b /uix', $query, $match);
		return reset($match['year']);
	}

	public function formName()
	{
		return '';
	}

	public function getSearchQuery($params = [])
	{

		$query = Auto::find();

		if (!$this->validate()) {

			$query
				->addSelect(new Expression('1 not_found_cond'))
				->andWhere('not_found_cond=0');
		}

		if (!$this->isEmpty($params['q'])) {

			$preparedWords = $this->prepareWords($params['q']);
			$query->byQ($preparedWords);

			if ((int)$params['year'] > 0)
				$query->byYear($params['year']);
		}

		return $query;
	}

	protected function isEmpty($value)
	{
		return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
	}

	public function search($params)
	{

		$this->load($params) && $this->validate();

		$params = $this->getSearchAttributes();

		// формируем запрос к базе
		$query = $this->getSearchQuery($params);

		return $query->all();
	}

	public function getSearchAttributes()
	{
		return $this->getAttributes($this->safeAttributes());
	}

	protected function prepareWords($query)
	{

		if (!is_array($query)) {

			$query = trim(preg_replace('/[\s\.,]+/ui', ' ', $query));
			$query = preg_split('/[\s]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
		}

		return $query;
	}

}
