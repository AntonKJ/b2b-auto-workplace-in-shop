<?php

namespace cp\controllers;

use common\models\DiskBrand;
use common\models\Region;
use cp\components\Controller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class DiskController extends Controller
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
					'delete' => ['POST'],
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

		$query = DiskBrand::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		return $this->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Lists all OrderTypeGroup models.
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function actionView($id)
	{

		$model = $this->findModel($id);

		$model->setScenario(Region::SCENARIO_ADMIN);

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		}

		return $this->render('view', [
			'model' => $model,
		]);
	}

	/**
	 * Finds the Region model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return DiskBrand
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{

		if (($model = DiskBrand::findOne(['d_producer_id' => $id])) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}
}
