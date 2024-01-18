<?php

namespace console\components;

use yii\base\Component;
use yii\base\Exception;
use yii\db\Connection;

class DeliveryZoneUtils extends Component
{

	/**
	 * @param Connection $dbConnection
	 * @throws \yii\db\Exception
	 * @throws Exception
	 */
	public static function populateDeliveryArea($dbConnection = null)
	{

		if ($dbConnection === null)
			$dbConnection = \Yii::$app->db;

		$transaction = $dbConnection->beginTransaction();

		try {

			$dbConnection->createCommand()
				->update('{{%delivery_zone}}', ['delivery_area' => null])
				->execute();
			
			$query = (new \yii\db\Query())
				->from('{{%delivery_zone}}');

			foreach ($query->each() as $row) {

				if (empty($row['Coords']) || mb_strlen($row['Coords']) < 10)
					continue;

				$geoData = explode("\n", $row['Coords']);
				$geoData = array_map(function ($v) {

					$v = explode(',', $v);
					return implode(' ', [trim($v[1]), trim($v[0])]);
				}, $geoData);

				if ($geoData[0] != $geoData[count($geoData) - 1])
					$geoData[] = $geoData[0];

				$geoData = 'ST_PolygonFromText(\'POLYGON((' . implode(',', $geoData) . '))\')';

				$dbConnection
					->createCommand()
					->update('{{%delivery_zone}}', [
						'delivery_area' => new \yii\db\Expression($geoData),
					], ['id' => $row['id']])
					->execute();
			}

			$transaction->commit();
		} catch (Exception $e) {

			$transaction->rollBack();
			throw $e;
		}
	}

}