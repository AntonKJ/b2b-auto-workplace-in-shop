<?php

namespace common\components\auth\events;

use yii\base\Event;

class RenameRoleEvent extends Event
{
    public $oldRoleName;
    public $newRoleName;
}