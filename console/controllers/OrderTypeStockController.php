<?php

namespace console\controllers;

use common\components\SphinxIndex;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\db\Exception;

class OrderTypeStockController extends Controller
{

	/**
	 * @return int
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 */
	public function actionPopulate()
	{

		/**
		 * @var Connection $dbConnection
		 */

		$dbConnection = \Yii::$app->db;
		//print_r($dbConnection);
/*
		$transaction = $dbConnection->beginTransaction();

		try {*/

			$sphinxIndex = \Yii::createObject(SphinxIndex::class);

			$this->stdout("Clear table `order_type_stock`...\n");
			$dbConnection->createCommand()->delete('{{%order_type_stock}}')->execute();

			$this->stdout("Calculating availability by order types for shops...\n");
			// Вычисляем наличие по типам заказа для магазинов
			$query = $dbConnection->createCommand($sphinxIndex->getPickupPopulateOrderTypeStockQueryString());
			//$this->stdout($query->getRawSql()."\n");
			$query->execute();
			$this->stdout("Ok...\n");

			$this->stdout("Calculating availability by order types for delivery...\n");
			// Вычисляем наличие по типам заказа для доставки
			$query = $dbConnection->createCommand($sphinxIndex->getDeliveryPopulateOrderTypeStockQueryString());
			//$this->stdout($query->getRawSql()."\n");
			$query->execute();
			$this->stdout("Ok...\n");
/*
			$transaction->commit();

			$this->stdout("All right!!!\n");
		} catch (Exception $e) {

			$transaction->rollBack();
			$this->stderr("Error!\n");
			throw $e;
		}*/

		return ExitCode::OK;
	}

}