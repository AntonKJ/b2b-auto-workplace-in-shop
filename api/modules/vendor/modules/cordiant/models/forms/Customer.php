<?php

namespace api\modules\vendor\modules\cordiant\models\forms;

use yii\base\Model;

class Customer extends Model
{

	public $firstname;
	public $lastname;
	public $phone;
	public $email;

	public function attributeLabels()
	{
		return [

		];
	}

	public function getFullname()
	{
		return trim(implode(' ', [$this->firstname, $this->lastname]));
	}

	public function rules()
	{
		return [

			[['firstname'], 'required'],
			[['firstname'], 'string', 'max' => 255],

			[['lastname'], 'required'],
			[['lastname'], 'string', 'max' => 255],

			[['phone'], 'required'],
			[['phone'], 'filter', 'filter' => function ($value) {

				return mb_substr(preg_replace('/[^\d]/', '', $value), -10);
			}],
			[['phone'], 'string', 'min' => 10, 'tooShort' => 'Телефон должен состоять минимум из 10 цифр'],

			[['email'], 'required'],
			[['email'], 'email'],

		];
	}

}