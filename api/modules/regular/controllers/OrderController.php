<?php

namespace api\modules\regular\controllers;

use api\modules\regular\components\Controller;
use api\modules\regular\components\responces\OrderCanceled;
use api\modules\regular\components\responces\OrderCreated;
use api\modules\regular\components\responces\OrderPlaceFailed;
use api\modules\regular\models\ApiUser;
use api\modules\regular\models\forms\OrderForm;
use common\components\webService\request\CancelOrder;
use common\components\webService\request\GetDataForInvoice;
use common\interfaces\B2BUserInterface;
use common\models\OptUser;
use domain\entities\service1c\OrderInvoiceRendererPdf;
use domain\entities\service1c\OrderReserve;
use domain\repositories\Order1cResponseException;
use domain\services\Service1c;
use Exception;
use Throwable;
use Yii;
use yii\base\ExitException;
use yii\base\InvalidArgumentException;
use yii\base\Module;
use yii\caching\TagDependency;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Request;
use yii\web\Response;

class OrderController extends Controller
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

		$behaviors['contentNegotiator']['formats']['application/pdf'] = Response::FORMAT_RAW;
		$behaviors['contentNegotiator']['formats']['application/xlsx'] = Response::FORMAT_RAW;

		$behaviors['verbs'] = [
			'class' => VerbFilter::class,
			'actions' => [
				'create' => ['POST'],
				'cancel' => ['PUT'],
				'invoice' => ['GET'],
			],
		];

		return $behaviors;
	}

	/**
	 * @return OrderCreated|OrderPlaceFailed|OrderForm
	 * @throws Throwable
	 */
	public function actionCreate()
	{

		$ecommerce = Yii::$app->ecommerce;

		/** @var B2BUserInterface|ApiUser $userIdentity */
		$userIdentity = Yii::$app->getUser()->getIdentity();

		$orderForm = new OrderForm($ecommerce, $userIdentity->region, $userIdentity);
		$orderForm->load($this->request->post(), '');

		/**
		 * @var bool|OrderReserve $result
		 */
		if (true === $orderForm->placeOrder()) {
			return new OrderCreated($orderForm->getReserve());
		}

		return new OrderPlaceFailed($orderForm);
	}


	/**
	 * @param $orderId
	 * @return OrderCanceled|CancelOrder
	 * @throws InvalidArgumentException
	 * @throws ForbiddenHttpException
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 */
	public function actionCancel($orderId)
	{

		/**
		 * @var ApiUser $user
		 */
		$user = Yii::$app->user->getIdentity();

		$request = new CancelOrder();
		$request->OrderNumber = $orderId;

		if (!$request->validate()) {
			return $request;
		}

		/**
		 * @var Service1c $service1c
		 */
		$service1c = Yii::$container->get(Service1c::class);

		try {
			$order = $service1c->getOrderInfo($user->code_1c, $request->OrderNumber);
		} catch (Exception $e) {
			throw new NotFoundHttpException('Заказ не существует.');
		}

		if ($order->getClientCode() !== $user->getClientCode())
			throw new ForbiddenHttpException('Заказ не пренадлежит текущему пользователю.');

		/**
		 * @var \common\components\webService\response\CancelOrder $result
		 */
		$result = $service1c->cancelOrder($user->code_1c, $request->OrderNumber);

		if ((bool)$result['status'] === true) {

			// Чистим кешь по текущему пользователю
			TagDependency::invalidate(Yii::$app->cache, ["orders-user-{$user->getId()}"]);
		}

		return new OrderCanceled($request->OrderNumber, $result);
	}


	/**
	 * @param $orderId
	 * @return GetDataForInvoice|\yii\console\Response|Response
	 * @throws HttpException
	 * @throws RangeNotSatisfiableHttpException
	 * @throws Throwable
	 */
	public function actionInvoice($orderId)
	{

		$request = new GetDataForInvoice();
		$request->OrderNumber = $orderId;

		if (!$request->validate()) {
			return $request;
		}

		/**
		 * @var OptUser $user
		 */
		$user = Yii::$app->getUser()->getIdentity();

		/**
		 * @var Service1c $service1c
		 */
		$service1c = Yii::$container->get(Service1c::class);

		try {
			$invoice = $service1c->getInvoice($user->code_1c, $request->OrderNumber);
		} catch (Order1cResponseException $e) {
			throw new HttpException(422, $e->getMessage());
		}

		$renderer = new OrderInvoiceRendererPdf($invoice);

		$renderer->docroot = Yii::getAlias('@storage/stamps');
		$renderer->xslTemplate = Yii::getAlias('@storage/xsl/invoice.xslt');
		$renderer->fontsPath = Yii::getAlias('@storage/fonts');

		$pdfData = $renderer->render();

		$date = date('Y-m-d');

		$response = Yii::$app->response;
		$response->sendContentAsFile($pdfData, "invoice_{$request->OrderNumber}_{$date}.pdf", [
			'mimeType' => FileHelper::getMimeTypeByExtension('.pdf'),
		]);

		return $response;
	}

}
