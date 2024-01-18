<?php

namespace api\controllers;

use api\components\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ErrorAction;

/**
 * Site controller
 */
class SiteController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['index', 'error'],
						'allow' => true,
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'error' => [
				'class' => ErrorAction::class,
			],
		];
	}

	/**
	 * @return array
	 */
	public function actionIndex()
	{
		return ['Myexample API © ' . date('Y')];
	}

}
