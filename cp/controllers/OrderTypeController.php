<?php

namespace cp\controllers;

use common\models\DeliveryZone;
use common\models\DeliveryZoneDeliveryCity;
use cp\components\Controller;
use cp\models\OrderType;
use cp\models\OrderTypeForm;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * OrderTypeController implements the CRUD actions for OrderType model.
 */
class OrderTypeController extends Controller
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
	 * Lists all OrderType models.
	 * @return mixed
	 */
	public function actionIndex()
	{

		$query = OrderType::find();

		$query
			->alias('ot')
			->addSelect([
				'ot.*',
				'zonesCount' => 'dz_cnt.cnt',
				'citiesCount' => 'dz_cnt.citiesCount',
			])
			->leftJoin([
				'dz_cnt' => DeliveryZone::find()
					->alias('dz')
					->select([
						'order_type_id',
						'cnt' => new Expression('COUNT(DISTINCT(id))'),
						'citiesCount' => new Expression('SUM(dc_cnt.cnt)'),
					])
					->leftJoin([
						'dc_cnt' => DeliveryZoneDeliveryCity::find()
							->select([
								'delivery_zone_id',
								'cnt' => new Expression('COUNT(delivery_city_id)'),
							])
							->groupBy(['delivery_zone_id']),
					], 'dc_cnt.delivery_zone_id = dz.id')
					->groupBy(['order_type_id']),
			], 'dz_cnt.order_type_id = ot.ot_id')
			->with([
				'groups',
				'deliveryZones',
				'metro',
			]);

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

		$model = $this->findModel($id);

		$formModel = new OrderTypeForm();
		$formModel->loadFromModel($model);

		if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {

			$transaction = Yii::$app->db->beginTransaction();

			try {

				$formModel->loadToModel($model);
				$model->save(false);

				$model->unlinkAll('metro', true);
				if (($metro = $formModel->getMetro()) !== null) {
					$model->link('metro', $metro);
				}

				$transaction->commit();
			} catch (Exception $e) {

				$transaction->rollBack();
				throw $e;
			}

			return $this->redirect(['index']);
		} else {
			return $this->render('update', [
				'model' => $model,
				'formModel' => $formModel,
			]);
		}
	}

	/**
	 * Finds the OrderType model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return OrderType the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = OrderType::findOne($id)) !== null) {
			return $model;
		}
		throw new NotFoundHttpException('The requested page does not exist.');
	}
}
