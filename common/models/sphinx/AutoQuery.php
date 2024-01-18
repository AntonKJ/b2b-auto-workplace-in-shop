<?php

namespace common\models\sphinx;

use yii\db\Expression;
use yii\sphinx\ActiveQuery;

class AutoQuery extends ActiveQuery
{

	public function byQ(array $words)
	{

		if ($words === [])
			return $this;

		$sphinx = $this->getConnection();

		$matchWords = [];
		foreach($words as $word)
			$matchWords[] = "*{$sphinx->escapeMatchValue($word)}*";

		$expression = new Expression(':match', [
			':match' => '(' . implode(') | (', $matchWords) . ')',
		]);

		return $this->match($expression);
	}

	public function byYear($year)
	{

		$this->andWhere([
			'and',
			['<=', 'modification_start', $year],
			['>=', 'modification_end', $year],
		]);

		return $this;
	}

}