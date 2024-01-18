<?php

namespace cp\models;

class DeliveryZone extends \common\models\DeliveryZone
{

	public $citiesCount;

	public function fields()
	{
		return [
			'id',
			'title',
			'email',
			'color',
			'geometry' => 'deliveryAreaArray',
			'is_published',
			'order_type_id',
		];
	}

	public function extraFields()
	{
		return [
			'cities',
		];
	}

}
