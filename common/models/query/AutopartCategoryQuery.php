<?php

namespace common\models\query;

use common\models\AutopartCategory;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\AutopartCategory]].
 *
 * @see \common\models\AutopartCategory
 */
class AutopartCategoryQuery extends ActiveQuery
{

	/**
	 * @inheritdoc
	 * @return AutopartCategory[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return AutopartCategory|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
