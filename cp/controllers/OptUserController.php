<?php

namespace cp\controllers;

use cp\components\Controller;
use cp\models\OptUser;
use Yii;
use yii\filters\VerbFilter;

class OptUserController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [

				],
			],
		];
	}

	/**
	 * Lists all OrderTypeGroup models.
	 * @return mixed
	 */
	public function actionIndex()
	{

		$searchModel = new OptUser();
		$dataProvider = $searchModel->search(Yii::$app->request->get());

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

}
