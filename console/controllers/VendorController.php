<?php

namespace console\controllers;

use api\models\VendorOrder;
use api\models\VendorUser;
use api\modules\vendor\modules\nokian\components\Order;
use common\components\webService\request\GetOrdersByVendor;
use common\components\webService\response\GetListOrders;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\db\Expression;
use yii\db\Query;
use yii\httpclient\Client;
use yii\httpclient\Request;

class VendorController extends Controller
{

	public const MAX_ATTEMPTS = 3;

	/**
	 * @throws \Exception
	 */
	public function actionIndex()
	{

		$data = [];
		$vendorUsers = VendorUser::getUserRepository();
		foreach ($vendorUsers as $user) {

			$data[] = [$user['id'], $user['username'], $user['vendor'], $user['status']];
		}

		echo Table::widget([
			'headers' => ['ID', 'Username', 'Vendor', 'Status'],
			'rows' => $data,
		]);

		return ExitCode::OK;
	}

	/**
	 * @param $vendor
	 * @return int
	 * @throws \yii\db\Exception
	 * @throws Exception
	 */
	public function actionUpdateOrderList($vendor)
	{

		if (null === ($vendorModel = VendorUser::findIdentityByVendor($vendor))) {

			$this->stderr("Vendor `{$vendor}` not exist.\n");
			return ExitCode::UNSPECIFIED_ERROR;
		}

		$request = new GetOrdersByVendor();
		$request->Vender = $vendorModel->getVendor();

		/**
		 * @var GetListOrders $response
		 */
		$response = Yii::$app->webservice->send($request);
		$orders = $response->getOrders();

		$orderIds = [];
		foreach ($orders as $order) {
			$orderIds[] = [
				'id' => $order->Number,
				'status' => $order->OrderStatus,
			];
		}

		$temporaryTable = "{{%vendor_order_{$vendorModel->getVendor()}_tmp}}";

		$dbConnection = Yii::$app->db;

		$dbConnection->createCommand("DROP TABLE if exists {$temporaryTable}")->execute();

		$dbConnection->createCommand("CREATE TEMPORARY TABLE {$temporaryTable} ( id integer, status varchar(32) )")->execute();

		$dbConnection->createCommand()
			->batchInsert($temporaryTable, ['id', 'status'], array_map('array_values', $orderIds))
			->execute();

		// Выбираем заказы для обновления или добавления
		$ordersToUpdate = (new Query())
			->select([
				'oid.id',
				'oid.status',
				'IF(vo.status IS NOT NULL AND vo.status != \'\', 1, 0) AS `exists`',
			])
			->from(['oid' => $temporaryTable])
			->leftJoin(['vo' => VendorOrder::tableName()], 'vo.order_id = oid.id')
			->andWhere('vo.status IS NULL OR vo.status = :status OR vo.status != oid.status', [':status' => ''])
			->all($dbConnection);

		$dbConnection->createCommand("DROP TABLE if exists {$temporaryTable}")->execute();

		//todo требуется оптимизация

		$status = [
			'created' => 0,
			'updated' => 0,
		];

		if ([] !== $ordersToUpdate) {
			$transaction = $dbConnection->beginTransaction();
			try {

				foreach ($ordersToUpdate as $order) {

					if ((int)$order['exists'] === 0) {

						$vendorOrder = new VendorOrder();

						$vendorOrder->vendor = $vendorModel->getVendor();

						$vendorOrder->order_id = $order['id'];
						$status['created']++;

						$this->stdout("\tAdd: {$order['id']}\n");

					} else {

						$vendorOrder = VendorOrder::find()
							->byVendor($vendorModel->getVendor())
							->byOrderId($order['id'])
							->one();

						$this->stdout("\tUpdate: {$order['id']}\n");

						$status['updated']++;
					}

					$vendorOrder->status = VendorOrder::mapStatusFrom1C($order['status']);
					$vendorOrder->save(false);

				}

				$transaction->commit();
			} catch (Exception $e) {

				$transaction->rollBack();
				throw $e;
			}
		}

		$this->stdout("Update order statuses for `{$vendorModel->getVendor()}`:\n");
		$this->stdout("\tAdd count: {$status['created']}\n");
		$this->stdout("\tUpdate count: {$status['updated']}\n");

		return ExitCode::OK;
	}

	/**
	 * @param $vendor
	 * @param int|null $count
	 * @return int
	 * @throws Exception
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public function actionNotify($vendor, ?int $count = null)
	{

		if ($count === null) {
			$count = 100;
		}

		if (null === ($vendorModel = VendorUser::findIdentityByVendor($vendor))) {
			$this->stderr("Vendor `{$vendor}` not exist.\n");
			return ExitCode::UNSPECIFIED_ERROR;
		}

		$reader = VendorOrder::find()
			->byVendor($vendorModel->getVendor())
			->byNotNotified()
			->byMaxAttempts(static::MAX_ATTEMPTS)
			->limit($count);

		$client = new Client([
			'baseUrl' => 'https://partners.nokiantyres.ru/vianorpartnerintegration', // prod
			//'baseUrl' => 'https://vianorqa.nokiantyres.ru/vianorpartnerintegration', // test
			'requestConfig' => [
				'format' => Client::FORMAT_XML,
			],
			'responseConfig' => [
				'format' => Client::FORMAT_XML,
			],
		]);

		/** @var Request[] $requests */
		$requests = [];

		$orderList = [];
		/**
		 * @var VendorOrder $order
		 */
		foreach ($reader->each() as $order) {

			$orderList[$order->order_id] = $order;

			$currentOrderStatus = Order::getVendorOrderStatusByApiStatus($order->status);
			if($currentOrderStatus === null || empty($currentOrderStatus)) {
				$this->stdout("Order `{$order->order_id}` unknown status.`{$order->status}`...\n");
				continue;
			}

			$data = [
				'partner-order-id' => $order->order_id,
				'entity' => 'ORDER',
				'action' => 'UPDATE',
				'order-status' => $currentOrderStatus,
			];
			if (VendorOrder::STATUS_CANCELLED === $order->status) {
				$data['reason'] = 'NOT_ENOUGH_PRODUCT';
			}
			$requests[$order->order_id] = $client->createRequest()
				->setMethod('POST')
				->setUrl('updateOrder')
				->addHeaders([
					'Authorization' => 'Basic ' . base64_encode($vendorModel->getAuthKey()),
				])
				->setData($data);
		}

		$updated = 0;
		if ([] !== $requests) {

			$responses = $client->batchSend($requests);

			$dbConnection = Yii::$app->db;
			$transaction = $dbConnection->beginTransaction();
			try {

				foreach ($responses as $orderId => $response) {

					$this->stdout(sprintf("OrderId `%s`:\n", $orderId));

					if (!$response->isOk) {

						$attemptsCount = (int)$orderList[$orderId]->attempts + 1;
						$this->stdout("\tstatus... FAILED\n");
						$this->stdout("\tattempts... {$attemptsCount}\n");

						$this->stdout("\trequest:\n");
						$this->stdout("\t{$requests[$orderId]->toString()}\n\n");

						$this->stdout("\tresponse:\n");
						$this->stdout("\t\tstatus: {$response->getStatusCode()}\n");
						$this->stdout("\t\tcontent: {$response->getContent()}\n\n");

						if ((int)$response->getStatusCode() === 400) {
							$dbConnection->createCommand()->update(VendorOrder::tableName(), [
								'attempts' => new Expression('attempts + 1'),
								'updated_at' => new Expression('NOW()'),
							], [
								'vendor' => $vendorModel->getVendor(),
								'order_id' => $orderId,
							])->execute();
						}

						Yii::info([
							$orderId,
							isset($requests[$orderId]) ? $requests[$orderId]->toString() : null,
							$response->getStatusCode(), $response->getContent(),
						], 'console.vendor.response');

						continue;
					}

					$this->stdout("\tstatus... OK\n\n");

					$dbConnection->createCommand()->update(VendorOrder::tableName(), [
						'notified_status' => new Expression('status'),
						'updated_at' => new Expression('NOW()'),
						'attempts' => 0,
					], [
						'vendor' => $vendorModel->getVendor(),
						'order_id' => $orderId,
					])->execute();

					$updated++;
				}
				$transaction->commit();
			} catch (Exception $e) {
				$transaction->rollBack();
				throw $e;
			}
		}

		$this->stdout("Notify `{$vendorModel->getVendor()}`:\n");

		$total = count($requests);
		$this->stdout("\tUpdate: {$updated} / {$total}\n");

		return ExitCode::OK;
	}

}
