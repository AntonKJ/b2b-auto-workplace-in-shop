<?php

namespace common\models;

use common\models\query\OrderTypeGroupQuery;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_type_group}}".
 *
 * @property integer $id
 * @property string $title
 */
class OrderTypeGroup extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%order_type_group}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['title'], 'required'],
			[['title'], 'string', 'max' => 255],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'title' => 'Название группы',
		];
	}

	/**
	 * @inheritdoc
	 * @return OrderTypeGroupQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new OrderTypeGroupQuery(static::class);
	}

	public function getOrderTypeGroupRels()
	{
		return $this->hasMany(OrderTypeGroupRel::class, ['group_id' => 'id']);
	}

	/**
	 * @return OrderTypeGroupQuery
	 */
	public function getOrderTypes()
	{
		return $this->hasMany(OrderType::class, ['ot_id' => 'order_type_id'])
			->via('orderTypeGroupRels');
	}

	/**
	 * Вычисляет пересечение между группами
	 * @param array $groups
	 * @return array
	 */
	static public function calculateOrderTypeGroupIntersect(array $groups)
	{

		static $cache = [];

		$groups = array_unique($groups);
		sort($groups);

		$key = md5(json_encode($groups));

		if (!isset($cache[$key])) {

			$cache[$key] = \Yii::$app->cache->getOrSet($key, function () use ($groups) {

				$query = OrderTypeGroupRel::find()
					->select(['order_type_id'])
					->andWhere(['group_id' => $groups]);

				if (\count($groups) > 1)
					$query
						->groupBy('order_type_id')
						->andHaving(new Expression('COUNT(DISTINCT [[group_id]]) = :cnt', [
							':cnt' => \count($groups),
						]));

				return $query->column();
			});
		}

		return $cache[$key];
	}
}
