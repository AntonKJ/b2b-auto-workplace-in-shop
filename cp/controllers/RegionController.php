<?php

namespace cp\controllers;

use common\models\Region;
use cp\components\Controller;
use cp\models\RegionSearch;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * OrderTypeGroupController implements the CRUD actions for OrderTypeGroup model.
 */
class RegionController extends Controller
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

		$searchModel = new RegionSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->get());

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Updates an existing OrderTypeGroup model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$model->setScenario(Region::SCENARIO_ADMIN);
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		}
		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Finds the Region model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Region the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = Region::findOne($id)) !== null) {
			return $model;
		}
		throw new NotFoundHttpException('The requested page does not exist.');
	}
}
