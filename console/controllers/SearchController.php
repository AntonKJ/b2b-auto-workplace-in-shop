<?php

namespace console\controllers;

use common\models\CatalogIndex;
use common\models\DiskBrand;
use common\models\DiskGood;
use common\models\DiskModel;
use common\models\TyreBrand;
use common\models\TyreGood;
use common\models\TyreModel;
use yii\console\Controller;
use yii\db\Connection;
use yii\db\Exception;
use yii\helpers\Console;

class SearchController extends Controller
{

	/**
	 * @param Connection $db
	 * @param array $data
	 * @return null|void
	 */
	protected function _storeData(Connection $db, array $data)
	{

		if ([] === $data)
			return;

		$firstRow = reset($data);

		$db
			->createCommand()
			->batchInsert(CatalogIndex::tableName(), array_keys($firstRow), $data)
			->execute();

		return;
	}

	public function actionIndexer()
	{

		/**
		 * @var Connection $dbConnection
		 * @var TyreGood $good
		 * @var TyreBrand $brand
		 * @var TyreModel $model
		 */

		$dbConnection = \Yii::$app->db;

		$transaction = $dbConnection->beginTransaction();

		try {

			$this->stdout("Очищаем таблицу!\n");
			$dbConnection->createCommand()->truncateTable(CatalogIndex::tableName())->execute();

			$brands = [];

			$brandsCount = TyreBrand::find()->count();
			Console::startProgress(0, $brandsCount, "Подготовка брендов шин: ");

			foreach (TyreBrand::find()->each(100) as $i => $brand) {

				$brands[mb_strtolower($brand->code)] = $brand;
				Console::updateProgress($i, $brandsCount);
			}

			Console::endProgress();

			$models = [];

			$modelsCount = TyreModel::find()->count();
			Console::startProgress(0, $modelsCount, "Подготовка моделей шин: ");

			foreach (TyreModel::find()->each(300) as $i => $model) {

				$models[mb_strtolower($model->prod_code)][mb_strtolower($model->code)] = $model;

				if (($i % 20) == 0)
					Console::updateProgress($i, $modelsCount);
			}

			Console::updateProgress($modelsCount, $modelsCount);
			Console::endProgress();

			$goodsCount = TyreGood::find()->count();
			Console::startProgress(0, $goodsCount, "Индексирование шин: ");

			$i = 0;
			$goods = [];
			foreach (TyreGood::find()->each(500) as $k => $good) {

				$indexRecord = [
					'entity_type' => TyreGood::GOOD_ENTITY_TYPE,
					'entity_id' => $good->primaryKey,
					'words' => $good->prepareSearchIndex,
					'brand_id' => null,
					'model_id' => null,
				];

				if (isset($brands[mb_strtolower($good->prod_code)])) {

					$brand = $brands[mb_strtolower($good->prod_code)];

					$indexRecord['words'] = array_merge($indexRecord['words'], $brand->prepareSearchIndex);
					$indexRecord['brand_id'] = (int)$brand->id;
				}

				if (isset($models[mb_strtolower($good->prod_code)][mb_strtolower($good->p_t)])) {

					$model = $models[mb_strtolower($good->prod_code)][mb_strtolower($good->p_t)];

					$indexRecord['words'] = array_merge($indexRecord['words'], $model->prepareSearchIndex);
					$indexRecord['model_id'] = (int)$model->id;
				}

				if (null === $indexRecord['brand_id'] || null === $indexRecord['model_id'])
					continue;

				$indexRecord['words'] = join(' ', array_unique($indexRecord['words']));

				$goods[] = $indexRecord;

				// Каждые n записей пишем данный в базу
				if ($i >= 1000) {

					$this->_storeData($dbConnection, $goods);
					$goods = [];

					$i = 0;
				}

				$i++;

				if (($k % 2000) == 0)
					Console::updateProgress($k, $goodsCount);
			}

			if ([] !== $goods)
				$this->_storeData($dbConnection, $goods);

			unset($brands, $models, $goods);

			Console::updateProgress($goodsCount, $goodsCount);
			Console::endProgress();

			// ДИСКИ ===================================================================================================

			$brands = [];

			$brandsCount = DiskBrand::find()->count();
			Console::startProgress(0, $brandsCount, "Подготовка брендов дисков: ");

			foreach (DiskBrand::find()->each(100) as $i => $brand) {

				$brands[(int)$brand->d_producer_id] = $brand;
				Console::updateProgress($i, $brandsCount);
			}

			Console::endProgress();

			$models = [];

			$modelsCount = DiskModel::find()->count();
			Console::startProgress(0, $modelsCount, "Подготовка моделей дисков: ");

			foreach (DiskModel::find()->each(300) as $i => $model) {

				$models[(int)$model->brand_id][(int)$model->id] = $model;

				if (($i % 20) == 0)
					Console::updateProgress($i, $modelsCount);
			}

			Console::updateProgress($modelsCount, $modelsCount);
			Console::endProgress();

			$goodsCount = DiskGood::find()->count();
			Console::startProgress(0, $goodsCount, "Индексирование дисков: ");

			$i = 0;
			$goods = [];
			foreach (DiskGood::find()->each(500) as $k => $good) {

				$indexRecord = [
					'entity_type' => DiskGood::GOOD_ENTITY_TYPE,
					'entity_id' => $good->primaryKey,
					'words' => $good->prepareSearchIndex,
					'brand_id' => null,
					'model_id' => null,
				];

				if (isset($brands[(int)$good->brand_id])) {

					$brand = $brands[(int)$good->brand_id];

					$indexRecord['words'] = array_merge($indexRecord['words'], $brand->prepareSearchIndex);
					$indexRecord['brand_id'] = (int)$good->brand_id;
				}

				if (isset($models[(int)$good->brand_id][(int)$good->model_id])) {

					$model = $models[(int)$good->brand_id][(int)$good->model_id];

					$indexRecord['words'] = array_merge($indexRecord['words'], $model->prepareSearchIndex);
					$indexRecord['model_id'] = (int)$good->model_id;
				}

				if (null === $indexRecord['brand_id'] || null === $indexRecord['model_id'])
					continue;

				$indexRecord['words'] = join(' ', array_unique($indexRecord['words']));

				$goods[] = $indexRecord;

				// Каждые n записей пишем данный в базу
				if ($i >= 1000) {

					$this->_storeData($dbConnection, $goods);
					$goods = [];

					$i = 0;
				}

				$i++;

				if (($k % 2000) == 0)
					Console::updateProgress($k, $goodsCount);
			}

			if ([] !== $goods)
				$this->_storeData($dbConnection, $goods);

			unset($brands, $models, $goods);

			Console::updateProgress($goodsCount, $goodsCount);
			Console::endProgress();

			$transaction->commit();
		} catch (Exception $e) {

			$transaction->rollBack();
			throw $e;
		}

	}

}