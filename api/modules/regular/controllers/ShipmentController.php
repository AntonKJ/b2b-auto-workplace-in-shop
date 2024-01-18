<?php

namespace api\modules\regular\controllers;

use api\modules\regular\components\Controller;
use api\modules\regular\components\responces\OrderDeliveryFailed;
use api\modules\regular\models\ApiUser;
use api\modules\regular\models\forms\OrderDeliveryForm;
use common\interfaces\B2BUserInterface;
use myexample\ecommerce\ArrayHelper;
use Throwable;
use Yii;
use yii\base\Module;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\web\Response;

class ShipmentController extends Controller
{

	/**
	 * @var \yii\console\Request|Request
	 */
	protected $request;

	/**
	 * OrderController constructor.
	 * @param string $id
	 * @param Module $module
	 * @param array $config
	 */
	public function __construct(string $id, Module $module, array $config = [])
	{
		parent::__construct($id, $module, $config);
		$this->request = Yii::$app->request;
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{

		$behaviors = parent::behaviors();

		$behaviors['verbs'] = [
			'class' => VerbFilter::class,
			'actions' => [
				'index' => ['POST'],
			],
		];

		return $behaviors;
	}

	/**
	 * @return OrderDeliveryFailed|array
	 * @throws Throwable
	 */
	public function actionIndex()
	{

		$ecommerce = Yii::$app->ecommerce;

		/** @var B2BUserInterface|ApiUser $userIdentity */
		$userIdentity = Yii::$app->getUser()->getIdentity();

		$orderDeliveryForm = new OrderDeliveryForm($ecommerce, $userIdentity->region, $userIdentity);
		$orderDeliveryForm->load($this->request->post(), '');

		/**
		 * @var bool|array $data
		 */
		if (false === ($data = $orderDeliveryForm->getDeliveryInfo())) {

			$data = (new OrderDeliveryFailed($orderDeliveryForm))->toArray();

			if (isset($data['reason']['goods']) && is_array($data['reason']['goods'])) {
				$data['reason']['goods'] = (object)$data['reason']['goods'];
			}

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->content = json_encode($data);

			return $response;

			//return new OrderDeliveryFailed($orderDeliveryForm);
		}

		return ArrayHelper::toArray($data);
	}

}
