<?php

namespace api\modules\vendor\modules\cordiant\models\forms;

use common\models\TyreGood;
use yii\base\Model;
use yii\db\conditions\InCondition;

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
				'targetClass' => TyreGood::class,
				'targetAttribute' => 'manuf_code',
				'message' => 'Товар с кодом `{value}` не найден.',
				'filter' => new InCondition('prod_code', 'IN', ['cordiant']),
			],

			[['quantity'], 'required'],
			[['quantity'], 'integer', 'min' => 1],

		];
	}

}