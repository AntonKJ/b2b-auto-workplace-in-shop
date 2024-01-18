<?php

namespace common\models;

use common\interfaces\RegionEntityInterface;
use common\models\query\SeoTextQuery;

/**
 * This is the model class for table "{{%SeoText}}".
 *
 * @property int|null $id
 * @property string $title
 * @property string $content
 * @property int|null $region_id
 * @property int|null $ord_num
 *
 * @property null|RegionEntityInterface $region
 */
class SeoText extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%seo_text}}';
	}

	/**
	 * @return SeoTextQuery|\yii\db\ActiveQuery
	 */
	public static function find()
	{
		return new SeoTextQuery(static::class);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getRegion()
	{
		return $this->hasOne(Region::class, ['region_id' => 'region_id']);
	}

	/**
	 * @return string|null
	 */
	public function getContent()
	{
		return trim($this->text);
	}

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'id' => function (self $model) {
				return (int)$model->id;
			},
			'keyword',
			'content' => function (self $model) {
				return trim($model->content);
			},
			'region_id' => function (self $model) {
				return ($_r = (int)$model->region_id) === 0 ? null : $_r;
			},
		];
	}
}
