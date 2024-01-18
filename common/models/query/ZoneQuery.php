<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\Zone]].
 *
 * @see \common\models\Zone
 */
class ZoneQuery extends \yii\db\ActiveQuery
{

	public function __construct(string $modelClass, array $config = [])
	{
		parent::__construct($modelClass, $config);
		$this
			->andWhere('[[zone_id]] > 0');
	}

	/**
	 * @inheritdoc
	 * @return \common\models\Zone[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\Zone|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
