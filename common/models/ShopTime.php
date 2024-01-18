<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_line_time}}".
 * @property int $time_id [int(11)]
 * @property string $time [varchar(5)]
 */
class ShopTime extends ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shop_line_time}}';
	}

	public function getId(): int
	{
		return (int)$this->time_id;
	}

	public function getHours(): int
	{
		return (int)explode(':', $this->time)[0];
	}

	public function getMinutes(): int
	{
		return (int)explode(':', $this->time)[1];
	}

	public function getText(): string
	{
		return sprintf('%02d:%02d', $this->getHours(), $this->getMinutes());
	}

	public function fields()
	{
		return [
			'id',
			'hour' => 'hours',
			'minute' => 'minutes',
			'text',
		];
	}

	/**
	 * @return array|self[]|array<self>
	 */
	public static function getTimeOptions(): array
	{
		static $data;
		if ($data === null) {
			$data = Yii::$app->getCache()->getOrSet(__METHOD__, static function () {
				$collection = [];
				/** @var self $model */
				foreach (static::find()->each() as $model) {
					$collection[$model->getId()] = $model;
				}
				return $collection;
			});
		}
		return $data;
	}

}
