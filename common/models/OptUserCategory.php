<?php

namespace common\models;

use common\interfaces\OrderTypeGroupableInterface;
use yii\db\ActiveRecord;

/**
 * Token Active Record model.
 *
 * @property integer $ou_category_id
 * @property string $name
 * @property integer $order_type_group_id
 *
 */
class OptUserCategory extends ActiveRecord implements OrderTypeGroupableInterface
{

	const SCENARIO_ADMIN = 'admin';

	const CATEGORY_ESHOP = 1; // Интернет-магазины
	const CATEGORY_SERVICE = 2;  // Сервисы
	const CATEGORY_REGION = 3;
	const CATEGORY_REGION_TC = 4;

	public function scenarios()
	{

		$scenarios = parent::scenarios();

		$scenarios[static::SCENARIO_ADMIN] = ['order_type_group_id'];

		return $scenarios;
	}

	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'title' => 'Название',
			'order_type_group_id' => 'Группа типов товаров',
		];
	}

	public function rules()
	{
		return [
			[['order_type_group_id'], 'exist', 'targetClass' => OrderTypeGroup::class, 'targetAttribute' => 'id', 'allowArray' => true],
		];
	}

	public function getOrderTypeGroupId()
	{
		return $this->order_type_group_id;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->ou_category_id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id)
	{
		$this->ou_category_id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->name;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title)
	{
		$this->name = $title;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(OptUser::class, ['ou_category_id' => 'ou_category_id'])
			->inverseOf('category');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrderTypeGroup()
	{
		return $this->hasOne(OrderTypeGroup::class, ['id' => 'order_type_group_id']);
	}

	/** @inheritdoc */
	public static function tableName()
	{
		return '{{%opt_user_category}}';
	}

}