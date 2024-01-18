<?php

namespace api\modules\vendor\modules\mosautoshina\models\forms;

use common\models\ShopStock;
use yii\base\Model;

class Good extends Model
{

	public $sku;
	public $quantity;

	public function attributeLabels()
	{
		return [

		];
	}

	public function rules()
	{
		return [

			[['sku'], 'required'],
			[['sku'], 'filter', 'filter' => 'strval'],
			[['sku'], 'string', 'max' => 255],

			[['sku'], 'exist',
				'targetClass' => ShopStock::class,
				'targetAttribute' => 'item_idx',
				'message' => 'Товар с кодом `{value}` не найден.',
			],

			[['quantity'], 'required'],
			[['quantity'], 'integer', 'min' => 1, 'max' => 80],

		];
	}

}