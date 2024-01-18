<?php

namespace api\modules\vendor\modules\toyo;

use Yii;

class Module extends \yii\base\Module
{

	public function init()
	{

		parent::init();
		Yii::configure($this, []);
	}
}