<?php

namespace api\modules\vendor\modules\nokian\models\forms;

use common\models\TyreGood;
use yii\base\Model;

class ProductAvailability extends Model
{

	public $code;

	public function rules()
	{
		return [

			[['code'], 'required'],
			[['code'], 'filter', 'filter' => 'strval'],
			[['code'], 'string', 'max' => 255],
			[['code'], 'exist', 'targetClass' => TyreGood::class, 'targetAttribute' => 'manuf_code', 'message' => 'Товар с кодом `{value}` не найден.'],

		];
	}

}
