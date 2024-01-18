<?php

namespace common\components;

use yii\db\ActiveRelationTrait;

trait ActiveQueryCustomizeTrait
{

    use ActiveRelationTrait {
        normalizeModelKey as _normalizeModelKey;
    }

    /**
     * @param $value
     * @return mixed|null|string|string[]
     */
    private function normalizeModelKey($value)
    {
        return mb_strtolower($this->_normalizeModelKey($value));
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        list($table, $alias) = $this->getTableNameAndAlias();
        return !empty($alias) ? $alias : $table;
    }

}
