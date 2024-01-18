<?php

namespace common\models;

use common\interfaces\RegionEntityInterface;
use common\models\query\NewsQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%news}}".
 *
 * @property int|null $id
 * @property string $title
 * @property string $content
 * @property int|null $region_id
 * @property int|null $ord_num
 *
 * @property null|RegionEntityInterface $region
 */
class News extends ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%news}}';
	}

	/**
	 * @return NewsQuery|ActiveQuery
	 */
	public static function find()
	{
		return new NewsQuery(static::class);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getRegion()
	{
		return $this->hasOne(Region::class, ['region_id' => 'region_id']);
	}

	/**
	 * @return int|null
	 */
	public function getId()
	{
		return $this->news_id;
	}

	/**
	 * @return string|null
	 */
	public function getContent()
	{
		return trim($this->body);
	}

	/**
	 * @return string|null
	 */
	public function getContentHash(): string
	{
		return md5($this->getContent());
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
			'title' => static function (self $model) {
				return trim($model->title);
			},
			'content' => static function (self $model) {
				return trim(preg_replace('/^(\<\s*br[\/\s]*\>\s*)*/ui', '', $model->content));
			},
			'content_hash' => static function (self $model) {
				return $model->getContentHash();
			},
			'region_id' => static function (self $model) {
				return ($_r = (int)$model->region_id) === 0 ? null : $_r;
			},
		];
	}
}
