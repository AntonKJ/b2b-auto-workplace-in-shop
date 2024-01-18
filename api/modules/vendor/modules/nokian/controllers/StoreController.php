<?php

namespace api\modules\vendor\modules\nokian\controllers;

use api\modules\vendor\modules\nokian\components\Controller;
use api\modules\vendor\modules\nokian\components\responces\StoreCheck;
use api\modules\vendor\modules\nokian\models\forms\OrderAvailableForm;
use domain\entities\service1c\OrderReserve;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\di\NotInstantiableException;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\web\Response;

class StoreController extends Controller
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
				'check' => ['POST'],
			],
		];

		return $behaviors;
	}

	/**
	 * @return OrderAvailableForm|Response
	 * @throws Throwable
	 */
	public function actionCheck()
	{

		$inputData = $this->request->post();

		$orderAvailable = new OrderAvailableForm($this->module->order, $this->availability, $this->delivery);
		$orderAvailable->load($inputData, '');

		if ($orderAvailable->validate()) {

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_XML;
			$response->content = (new StoreCheck($orderAvailable))->toXmlString();

			return $response;
		}

		return $orderAvailable;
	}

}
