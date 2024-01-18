<?php

namespace api\modules\vendor\modules\cordiant\controllers;

use api\components\VendorOrderBuilder;
use api\models\VendorUser;
use api\modules\vendor\modules\cordiant\components\Controller;
use api\modules\vendor\modules\cordiant\components\responces\OrderPlaceFailed;
use api\modules\vendor\modules\cordiant\components\responces\OrderCreated;
use api\modules\vendor\modules\cordiant\models\forms\OrderForm;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use yii\base\Module;
use yii\filters\VerbFilter;

class OrderController extends Controller
{

	/**
	 * @var \yii\console\Request|\yii\web\Request
	 */
	protected $request;
	protected $delivery;

	/**
	 * @var GoodAvailabilityServiceInterface
	 */
	protected $availability;

	/**
	 * OrderController constructor.
	 * @param string $id
	 * @param Module $module
	 * @param array $config
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\di\NotInstantiableException
	 */
	public function __construct(string $id, Module $module, array $config = [])
	{
		parent::__construct($id, $module, $config);

		$this->request = \Yii::$app->request;
		$this->delivery = \Yii::$app->delivery;
		$this->availability = \Yii::$container->get(GoodAvailabilityService::class);
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
				'create' => ['POST'],
			],
		];

		return $behaviors;
	}

	/**
	 * @return OrderCreated|OrderPlaceFailed|OrderForm
	 * @throws \Throwable
	 */
	public function actionCreate()
	{

		$inputData = $this->request->post();

		$order = new OrderForm($this->module->order, $this->availability, $this->delivery);
		$order->load($inputData, '');

		/**
		 * @var bool|\domain\entities\service1c\OrderReserve $result
		 */
		if (false !== ($result = $this->module->order->placeOrder($order))) {

			/**
			 * @var VendorUser $user
			 */
			$user = \Yii::$app->user->getIdentity();

			$vendorOrder = (new VendorOrderBuilder($result, $user))->create();
			$vendorOrder->save(false);

			return new OrderCreated($result);
		}

		return new OrderPlaceFailed($order);
	}

}