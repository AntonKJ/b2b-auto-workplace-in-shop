<?php

namespace cp\components;

use Yii;

class Controller extends \yii\web\Controller
{

	public function beforeAction($action)
	{

		$this->getView()->params['config'] = [
			'csrf' => [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken],
			'csrfParams' => Yii::$app->request->csrfParam,
			'csrfToken' => Yii::$app->request->csrfToken,
		];

		return parent::beforeAction($action);
	}

}