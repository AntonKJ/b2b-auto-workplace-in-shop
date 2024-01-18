<?php

namespace api\modules\vendor\modules\goodyear;

use Yii;

class Module extends \yii\base\Module
{

	public function init()
	{

		parent::init();
		Yii::configure($this, []);
	}
}