<?php

namespace console\controllers;

use console\components\DeliveryZoneUtils;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;

class DeliveryZoneController extends Controller
{

	/**
	 * @return int
	 * @throws \yii\db\Exception
	 * @throws Exception
	 */
	public function actionPopulateDeliveryArea()
	{

		DeliveryZoneUtils::populateDeliveryArea(\Yii::$app->db);
		return ExitCode::OK;
	}

}