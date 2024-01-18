<?php

namespace common\models;

use common\models\query\NotifyMessageProjectQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%notify_message_project}}".
 *
 * @property int|null $id
 * @property string $title
 */
class NotifyMessageProject extends ActiveRecord
{

	public const PROJECT_RETAIL = 'retail';
	public const PROJECT_B2B = 'b2b';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%notify_message_project}}';
	}

	/**
	 * @return NotifyMessageProjectQuery|ActiveQuery
	 */
	public static function find()
	{
		return new NotifyMessageProjectQuery(static::class);
	}

	/**
	 * @return array|string[]
	 */
	public static function getProjectOptions(): array
	{
		return [
			static::PROJECT_RETAIL => 'Розничный',
			static::PROJECT_B2B => 'Б2Б',
		];
	}

	/**
	 * @return string
	 */
	public function getProjectText(): string
	{
		return static::getProjectOptions()[$this->project] ?? 'Неизвестный проект';
	}

}
