<?php

namespace api\modules\vendor\modules\mosautoshina\controllers;

use api\components\VendorOrderBuilder;
use api\models\VendorUser;
use api\modules\vendor\modules\mosautoshina\components\Controller;
use api\modules\vendor\modules\mosautoshina\components\responces\OrderCanceled;
use api\modules\vendor\modules\mosautoshina\components\responces\OrderCreated;
use api\modules\vendor\modules\mosautoshina\components\responces\OrderPlaceFailed;
use api\modules\vendor\modules\mosautoshina\models\forms\OrderForm;
use common\components\webService\request\CancelOrder;
use common\models\OptUser;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use domain\services\Service1c;
use Yii;
use yii\base\Module;
use yii\caching\TagDependency;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

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
				'cancel' => ['PUT'],
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

		/**
		 * @var VendorUser $user
		 */
		$user = \Yii::$app->user->getIdentity();

		if ($user->getOptUser() === null)
			throw new NotFoundHttpException('B2B пользователь не найден.');

		$this->module->order->setUser($user->getOptUser());

		$inputData = $this->request->post();

		$order = new OrderForm($this->module->order, $this->availability, $this->delivery);
		$order->load($inputData, '');

		/**
		 * @var bool|\domain\entities\service1c\OrderReserve $result
		 */
		if (false !== ($result = $this->module->order->placeOrder($order))) {

			$vendorOrder = (new VendorOrderBuilder($result, $user))->create();
			$vendorOrder->save(false);

			return new OrderCreated($result);
		}

		return new OrderPlaceFailed($order);
	}


	/**
	 * @param $orderId
	 * @return OrderCanceled|CancelOrder
	 * @throws \yii\base\InvalidArgumentException
	 * @throws ForbiddenHttpException
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 */
	public function actionCancel($orderId)
	{

		/**
		 * @var VendorUser $user
		 */
		$user = \Yii::$app->user->getIdentity();

		if ($user->getOptUser() === null)
			throw new NotFoundHttpException('B2B пользователь не найден.');

		$request = new CancelOrder();
		$request->OrderNumber = $orderId;

		if (!$request->validate())
			return $request;

		/**
		 * @var OptUser $userB2b
		 */
		$userB2b = $user->getOptUser();

		/**
		 * @var Service1c $service1c
		 */
		$service1c = \Yii::$container->get(Service1c::class);

		try {
			$order = $service1c->getOrderInfo($userB2b->code_1c, $request->OrderNumber);
		} catch (\Exception $e) {
			throw new NotFoundHttpException('Заказ не существует.');
		}

		if ($order->getClientCode() !== $userB2b->getClientCode())
			throw new ForbiddenHttpException('Заказ не пренадлежит текущему пользователю.');

		/**
		 * @var \common\components\webService\response\CancelOrder $result
		 */
		$result = $service1c->cancelOrder($userB2b->code_1c, $request->OrderNumber);

		if ((bool)$result['status'] === true) {

			// Чистим кешь по текущему пользователю
			TagDependency::invalidate(Yii::$app->cache, ["orders-user-{$userB2b->getId()}"]);
		}

		return new OrderCanceled($request->OrderNumber, $result);
	}

}