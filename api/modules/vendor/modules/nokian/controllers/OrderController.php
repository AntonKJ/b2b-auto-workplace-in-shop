<?php

namespace api\modules\vendor\modules\nokian\controllers;

use api\components\VendorOrderBuilder;
use api\models\VendorUser;
use api\modules\vendor\modules\nokian\components\Controller;
use api\modules\vendor\modules\nokian\components\responces\OrderCancelled;
use api\modules\vendor\modules\nokian\components\responces\OrderCancelledNotEnoughProduct;
use api\modules\vendor\modules\nokian\components\responces\OrderCreated;
use api\modules\vendor\modules\nokian\models\forms\OrderCancelForm;
use api\modules\vendor\modules\nokian\models\forms\OrderForm;
use domain\entities\service1c\OrderReserve;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use domain\services\Service1c;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\di\NotInstantiableException;
use yii\filters\VerbFilter;
use yii\web\Request;

class OrderController extends Controller
{

	/**
	 * @var \yii\console\Request|Request
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
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 */
	public function __construct(string $id, Module $module, array $config = [])
	{
		parent::__construct($id, $module, $config);

		$this->request = Yii::$app->request;
		$this->delivery = Yii::$app->delivery;
		$this->availability = Yii::$container->get(GoodAvailabilityService::class);
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
				'status' => ['POST'],
			],
		];

		return $behaviors;
	}

	/**
	 * @return OrderCancelledNotEnoughProduct|OrderCreated|array
	 * @throws Throwable
	 */
	public function actionCreate()
	{

		$inputData = $this->request->post();

		$order = new OrderForm($this->module->order, $this->availability, $this->delivery);
		$order->load($inputData, '');

		/**
		 * @var bool|OrderReserve $result
		 */
		if (false !== ($result = $this->module->order->placeOrder($order))) {

			/**
			 * @var VendorUser $user
			 */
			$user = Yii::$app->user->getIdentity();

			$vendorOrder = (new VendorOrderBuilder($result, $user))->create();
			$vendorOrder->save(false);

			return new OrderCreated($result);
		}

		return new OrderCancelledNotEnoughProduct($order);
	}

	/**
	 * @return OrderCancelled|OrderCancelForm
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 * @throws Throwable
	 */
	public function actionStatus()
	{

		$inputData = $this->request->post();

		/**
		 * @var Service1c $service1c
		 */
		$service1c = Yii::$container->get(Service1c::class);
		/**
		 * @var VendorUser $user
		 */
		$user = Yii::$app->user->getIdentity();

		$orderCancelForm = new OrderCancelForm($user, $service1c);
		$orderCancelForm->load($inputData, '');

		if (false === $orderCancelForm->cancelOrder()) {
			return $orderCancelForm;
		}

		return new OrderCancelled($orderCancelForm);
	}

}
