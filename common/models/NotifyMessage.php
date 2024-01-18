<?php

namespace common\models;

use common\models\query\NotifyMessageProjectQuery;
use common\models\query\NotifyMessageQuery;
use common\models\query\RegionQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%notify_message}}".
 *
 * @property int|null $id
 * @property string $title
 * @property string $type
 *
 * @property string $statusText
 * @property string $typeText
 * @property ActiveQuery|NotifyMessageProjectQuery $projects
 * @property ActiveQuery|RegionQuery $regions
 */
class NotifyMessage extends ActiveRecord
{

	public const STATUS_PUBLISHED = 1;
	public const STATUS_DRAFT = 0;

	public const IS_PINNED = 1;

	public const TYPE_INFO = 'info';
	public const TYPE_WARNING = 'warning';
	public const TYPE_DANGER = 'danger';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%notify_message}}';
	}

	/**
	 * @return NotifyMessageQuery|ActiveQuery
	 */
	public static function find()
	{
		return new NotifyMessageQuery(static::class);
	}

	/**
	 * @return array|string[]
	 */
	public static function getStatusOptions(): array
	{
		return [
			static::STATUS_PUBLISHED => 'Опубликовано',
			static::STATUS_DRAFT => 'Черновик',
		];
	}

	/**
	 * @return string
	 */
	public function getStatusText(): string
	{
		return static::getStatusOptions()[$this->status] ?? 'Неизвестный статус';
	}


	/**
	 * @return array|string[]
	 */
	public static function getTypeOptions(): array
	{
		return [
			static::TYPE_INFO => 'Информационное сообщение',
			static::TYPE_WARNING => 'Предупреждение',
			static::TYPE_DANGER => 'Очень важное сообщение',
		];
	}

	/**
	 * @return string
	 */
	public function getTypeText(): string
	{
		return static::getTypeOptions()[$this->type] ?? 'Неизвестный тип';
	}

	/**
	 * @return string
	 */
	public function getHash(): string
	{
		return md5(implode('|', [
			$this->id,
			$this->type,
			$this->title,
			$this->announce,
			$this->content,
		]));
	}

	public function isPinned(): bool
	{
		return (int)$this->is_pinned === static::IS_PINNED;
	}

	/**
	 * @return ActiveQuery
	 */
	public function getRegionsRel()
	{
		return $this->hasMany(NotifyMessageRegion::class, ['notify_message_id' => 'id']);
	}

	/**
	 * @return RegionQuery|ActiveQuery
	 */
	public function getRegions()
	{
		return $this->hasMany(Region::class, ['region_id' => 'region_id'])
			->via('regionsRel');
	}

	/**
	 * @return NotifyMessageProjectQuery|ActiveQuery
	 */
	public function getProjects()
	{
		return $this->hasMany(NotifyMessageProject::class, ['notify_message_id' => 'id']);
	}

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'id' => static function (self $model) {
				return (int)$model->id;
			},
			'type',
			'typeText',
			'title',
			'announce',
			'content',
			'hash',
			'isPinned' => static function (self $model) {
				return $model->isPinned();
			},
		];
	}
}
