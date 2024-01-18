<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\AutoImagesQuery]].
 *
 * @see \common\models\AutoImages
 */
class AutoImageQuery extends \yii\db\ActiveQuery
{

	/**
	 * @return AutoImageQuery
	 */
	public function defaultOrder()
	{
		return $this;
	}
	/**
	 * @inheritdoc
	 * @return \common\models\AutoBrand[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return \common\models\AutoBrand|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
