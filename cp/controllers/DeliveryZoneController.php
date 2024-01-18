<?php

namespace cp\controllers;

use common\models\DeliveryCity;
use common\models\DeliveryZoneDeliveryCity;
use cp\components\Controller;
use cp\models\DeliveryZone;
use cp\models\OrderTypeDeliveryCityForm;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DeliveryZoneController extends Controller
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
					'city-add' => ['POST'],
					'city-remove' => ['POST'],
				],
			],
		];
	}

	public function actionIndex()
	{

		$query = DeliveryZone::find();

		$query
			->alias('dz')
			->addSelect([
				'dz.*',
				'citiesCount' => 'dc_rel.cnt',
			])
			->leftJoin([
				'dc_rel' => DeliveryZoneDeliveryCity::find()
					->select([
						'delivery_zone_id',
						'cnt' => new Expression('COUNT(delivery_city_id)'),
					])
					->groupBy(['delivery_zone_id']),
			], 'dc_rel.delivery_zone_id = dz.id')
			->with(['orderType']);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		return $this->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Updates an existing OrderType model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 * @throws Exception
	 * @throws NotFoundHttpException
	 */
	public function actionUpdate($id)
	{

		/** @var DeliveryZone $model */
		$model = $this->findModel($id);
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {

			$transaction = Yii::$app->db->beginTransaction();
			try {
				$model->save(false);
				$transaction->commit();
			} catch (Exception $e) {
				$transaction->rollBack();
				throw $e;
			}

			return $this->redirect(['index']);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Finds the OrderType model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return DeliveryZone|null the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id): ?DeliveryZone
	{
		if (($model = DeliveryZone::findOne($id)) !== null) {
			return $model;
		}
		throw new NotFoundHttpException('The requested page does not exist.');
	}

	/**
	 * @param $id
	 * @return OrderTypeDeliveryCityForm|Response
	 * @throws HttpException
	 * @throws NotFoundHttpException
	 */
	public function actionCityAdd($id)
	{

		/** @var DeliveryZone $model */
		$model = $this->findModel((int)$id);

		$formModel = new OrderTypeDeliveryCityForm();
		if (!$formModel->load(Yii::$app->request->post())) {
			throw new HttpException(400);
		}

		if ($formModel->validate()) {
			$model->link('cities', $formModel->getCity());
			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $formModel;
	}

	/**
	 * @param $id
	 * @param $city
	 * @return Response
	 * @throws NotFoundHttpException
	 */
	public function actionCityRemove($id, $city)
	{
		/** @var DeliveryZone $model */
		$model = $this->findModel((int)$id);

		if (($city = DeliveryCity::findOne((int)$city)) === null) {
			throw new NotFoundHttpException('The requested page does not exist.');
		}

		$model->unlink('cities', $city, true);
		return $this->redirect(['update', 'id' => $model->id]);
	}
}
