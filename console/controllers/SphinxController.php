<?php

namespace console\controllers;

use common\components\SphinxIndex;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

class SphinxController extends Controller
{

	public function actionConfig()
	{

		echo $this->renderPartial('config', [
			'sphinxConfig' => Yii::$app->sphinxConfig,
		]);

		return ExitCode::OK;
	}

	/**
	 * @param array $indexes
	 * @param int $startDocIndex
	 * @return int
	 * @throws InvalidConfigException
	 * @throws Exception
	 */
	public function actionQuery(array $indexes = ['tyre', 'disk'], int $startDocIndex = 0)
	{

		/** @var SphinxIndex $sphinxIndex */
		$sphinxIndex = Yii::createObject(SphinxIndex::class);

		$dbConnection = Yii::$app->db;

		$dbConnection->createCommand('SET SESSION query_cache_type=OFF;SET SESSION group_concat_max_len = 60000;')->execute();

		$indexeSuffix = implode('_', $indexes);

		$sqls = [
			'tyre' => $sphinxIndex->getUpdateSphinxIndexTyreQueryString([], true, $indexeSuffix),
			'disk' => $sphinxIndex->getUpdateSphinxIndexDiskQueryString([], true, $indexeSuffix),
		];

		if ($indexes !== []) {
			$sqls = array_intersect_key($sqls, array_fill_keys($indexes, null));
		}

		$sql = [
			'SET SESSION query_cache_type=OFF',
			'SET SESSION group_concat_max_len = 60000',
			$sphinxIndex->getQueryCreateTemporaryTableOrderTypeGroups($indexeSuffix),
			$sphinxIndex->getQueryCreateTemporaryTableShopGroups($indexeSuffix),
			$sphinxIndex->getQueryCreateTemporaryTableOrderTypeStock($indexeSuffix),
			"SET @cnt := {$startDocIndex}",
		];

		$sql[] = '(' . implode(') UNION (', $sqls) . ')';

		$this->stdout($dbConnection->createCommand(implode(";\n", $sql))->getRawSql());

		return ExitCode::OK;
	}

}