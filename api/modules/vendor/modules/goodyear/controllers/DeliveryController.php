<?php

namespace api\modules\vendor\modules\goodyear\controllers;

use api\modules\vendor\modules\goodyear\components\Controller;
use api\modules\vendor\modules\goodyear\models\Region;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class DeliveryController extends Controller
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
				'index' => ['GET'],
			],
		];

		return $behaviors;
	}

	public function actionIndex()
	{

		$regions = Region::find()
			->byZoneType(Region::ZONE_TYPE_WWW)
			->defaultOrder()
			->all();

		return ['response' => ['regions' => $regions]];
	}

	/**
	 * @param $regionId
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function actionView($regionId)
	{

		$delivery = Region::find()
			->byZoneType(Region::ZONE_TYPE_WWW)
			->byId($regionId)
			->one();

		if ($delivery === null)
			throw new NotFoundHttpException('Регион не найден.');

		return ['response' => ['delivery' => $delivery]];
	}

}