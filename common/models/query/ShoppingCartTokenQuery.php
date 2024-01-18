<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\ShoppingCartToken]].
 *
 * @see \common\models\ShoppingCartToken
 */
class ShoppingCartTokenQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param string $token
	 * @return $this
	 */
	public function byToken(string $token)
	{
		return $this->andWhere(['token' => $token]);
	}

    /**
     * @inheritdoc
     * @return \common\models\ShoppingCartToken[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\ShoppingCartToken|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
