<?php

namespace common\models\query;

use common\models\NotifyMessageProject;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[NotifyMessageProjectQuery]].
 *
 * @see NotifyMessageProject
 */
class NotifyMessageProjectQuery extends ActiveQuery
{

	/**
	 * @param string|string[]|array $project
	 * @return $this
	 */
	public function byProject($project): NotifyMessageProjectQuery
	{
		return $this
			->andWhere([
				'[[project]]' => $project,
			]);
	}

	/**
	 * @inheritdoc
	 * @return NotifyMessageProject[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return NotifyMessageProject|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
