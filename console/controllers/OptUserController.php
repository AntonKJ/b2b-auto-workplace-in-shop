<?php

namespace console\controllers;

use common\models\OptUser;
use common\models\OptUserToken;
use common\models\query\OptUserTokenQuery;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

class OptUserController extends Controller
{

	/**
	 * Генерируются токены для доступа к API для узеров у которых нету API токенов
	 * @return int
	 * @throws \yii\base\Exception
	 */
	public function actionGenerateApiTokens()
	{

		$db = Yii::$app->db;

		$counter = 0;

		$transaction = $db->beginTransaction();
		try {

			$reader = OptUser::find()
				->alias('ou')
				->select(['ou.[[id]]'])
				->joinWith(['apiToken' => function (OptUserTokenQuery $q) {
					$q->alias('t');
				}], false)
				->andWhere('t.[[id]] IS NULL')
				->asArray();

			$security = Yii::$app->security;
			foreach ($reader->batch(100) as $users) {

				$data = [];
				foreach ($users as $user) {

					$data[] = [
						'user_id' => $user['id'],
						'type' => OptUserToken::TYPE_API,
						'code' => $security->generateRandomString(),
						'created_at' => date('Y-m-d H:i:s'),
					];
				}

				$db->createCommand()
					->batchInsert(OptUserToken::tableName(), ['user_id', 'type', 'code', 'created_at'], $data)
					->execute();

				$counter += count($data);
			}

			$transaction->commit();
		} catch (Exception $e) {

			$transaction->rollBack();
			throw $e;
		}

		$this->stdout("Сгенерировано токенов: {$counter}\n");
		return ExitCode::OK;
	}

}