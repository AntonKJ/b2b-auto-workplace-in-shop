<?php

namespace cp\controllers;

use common\models\Region;
use cp\components\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class DeliveryController extends Controller
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
	 * @param null $region_id
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionIndex($region_id = null)
	{

		if (null == $region_id || empty($region_id) || (int)$region_id === 0) {
			return $this->render('no-region-selected', []);
		}

		$region = Region::find()->byId((int)$region_id)->one();
		if ($region === null) {
			throw new NotFoundHttpException('Регион с таким ID не найден!');
		}

		return $this->render('index', [
			'region' => $region,
		]);
	}

}
