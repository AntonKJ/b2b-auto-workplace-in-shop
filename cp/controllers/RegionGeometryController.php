<?php

namespace cp\controllers;

use cp\components\Controller;
use Yii;

class RegionGeometryController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [

		];
	}

	public function actionIndex()
	{
		$this->getView()->params['config']['apiUrl'] = Yii::$app->params['apiConfig']['endPointUrl'];
		return $this->render('index', []);
	}
}
