<?php

namespace cp\models;

use common\models\DeliveryCity;
use yii\base\Model;

class OrderTypeDeliveryCityForm extends Model
{

	public $city_id;

	public function rules()
	{
		return [
			[['city_id'], 'exist', 'targetClass' => DeliveryCity::class, 'targetAttribute' => 'id'],
		];
	}

	public function attributeLabels()
	{
		return [
			'city_id' => 'Город',
		];
	}

	/**
	 * @return DeliveryCitySearch|null
	 */
	public function getCity(): ?DeliveryCity
	{
		return DeliveryCity::findOne($this->city_id);
	}

}
