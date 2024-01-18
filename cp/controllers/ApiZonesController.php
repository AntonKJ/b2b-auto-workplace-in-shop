<?php

namespace cp\controllers;

use cp\models\DeliveryZone;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;

class ApiZonesController extends Controller
{

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{

		$behaviors = parent::behaviors();

		$behaviors['verbs'] = [
			'class' => VerbFilter::class,
			'actions' => [
				'GET' => 'index',
			],
		];

		unset($behaviors['rateLimiter']);

		return $behaviors;
	}

	public function actionIndex()
	{
		$query = DeliveryZone::find()->addSelectAreaAsJson();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => false,
		]);
		return $dataProvider;
	}

}
