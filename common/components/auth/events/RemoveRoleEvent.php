<?php

namespace common\components\auth\events;

use yii\base\Event;

class RemoveRoleEvent extends Event
{
    public $roleName;
}