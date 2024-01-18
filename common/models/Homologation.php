<?php

namespace common\models;

/**
 * This is the model class for table "{{%homologation}}".
 */
class Homologation extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%homologation}}';
	}

	public static function getHomologationOptions()
	{
		return \Yii::$app->getCache()->getOrSet('homologation.options', function () {

			$query = Homologation::find()->addSelect(['code', 'name'])->orderBy('LENGTH(code) DESC, code ASC')->asArray();

			$data = [];
			foreach ($query->each() as $row)
				$data[$row['code']] = $row['name'];

			return $data;
		}, 0);
	}

}
