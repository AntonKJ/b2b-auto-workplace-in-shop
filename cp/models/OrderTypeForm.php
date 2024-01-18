<?php

namespace cp\models;

use common\models\Metro;
use common\models\OrderType;
use yii\base\Model;

class OrderTypeForm extends Model
{

	public $metro_id;
	public $category;

	public function loadFromModel(OrderType $orderType)
	{

		$this->category = $orderType->category;
		$this->metro_id = $orderType->metro instanceof Metro ? $orderType->metro->id : null;

		return $this;
	}

	public function loadToModel(OrderType $orderType)
	{
		return $orderType->setAttributes($this->getAttributes(['category']));
	}

	public function rules()
	{
		return [
			[['metro_id'], 'exist', 'targetClass' => Metro::class, 'targetAttribute' => 'id'],
			[['category'], 'in', 'range' => array_keys(OrderType::getCategoryOptions())],
		];
	}

	public function attributeLabels()
	{
		return [
			'metro_id' => 'Привязка станций метро',
			'category' => 'Категория типа доставки',
		];
	}

	/**
	 * @return Metro|null
	 */
	public function getMetro(): ?Metro
	{
		return (int)$this->metro_id > 0 ? Metro::findOne($this->metro_id) : null;
	}

}
