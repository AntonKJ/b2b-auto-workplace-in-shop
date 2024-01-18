<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[OrderTypeGroup]].
 *
 * @see OrderTypeGroup
 */
class OrderTypeGroupQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return OrderTypeGroup[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderTypeGroup|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
