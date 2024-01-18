<?php

namespace common\models\forms;

use yii\base\Model;

class OrderForm extends Model
{

	const TYPE_FIZ = 10;
	const TYPE_UR = 20;

	public $name_family;
	public $name_first;
	public $name_patronymic;

	public $type;

	public $email;
	public $phone;
	public $phone_second;
	public $phone_ext_code;

	public $organization;
	public $inn;
	public $kpp;
	public $bik;
	public $rs;
	public $ur_adress;

	static public function getTypeOptions()
	{
		return [
			static::TYPE_FIZ => 'Физическое лицо',
			static::TYPE_UR => 'Юридическое лицо (или ИП)',
		];
	}

	public function attributeLabels()
	{
		return [
			'name_family' => 'Фамилия',
			'name_first' => 'Имя',
			'name_patronymic' => 'Отчество',
			'type' => 'Тип клиента',
			'email' => 'Адрес эл. почты',
			'phone' => 'Мобильный телефон',
			'phone_second' => 'Дополнительный телефон',
			'phone_ext_code' => 'Добавочный код',
			'organization' => 'Название организации',
			'inn' => 'ИНН',
			'kpp' => 'КПП',
			'bik' => 'БИК',
			'rs' => 'Расчетный счет',
			'ur_adress' => 'Юридический адрес',
		];
	}

	public function rules()
	{
		return [

			[['name_family', 'name_first', 'name_patronymic', 'phone', 'phone_second', 'phone_ext_code'], 'trim'],

			[['name_family'], 'required'],
			[['name_family'], 'string', 'max' => 255],

			[['name_first'], 'required'],
			[['name_first'], 'string', 'max' => 255],

			[['name_patronymic'], 'required'],
			[['name_patronymic'], 'string', 'max' => 255],

			[['type'], 'required'],
			[['type'], 'in', 'range' => array_keys(static::getTypeOptions())],

			[['phone', 'phone_second', 'phone_ext_code'], 'filter', 'filter' => function ($v) {
				return preg_replace('/[^\d]/ui', '', $v);
			}],
			[['phone'], 'required'],
			[['phone', 'phone_second'], 'string', 'length' => 11, 'message' => 'Телефон должен содержать 11 цифр'],
			[['phone_ext_code'], 'string', 'max' => 16],

			[['email'], 'required'],
			[['email'], 'string', 'max' => 255],
			[['email'], 'email'],

			[['organization', 'ur_address', 'inn', 'kpp', 'rs', 'bik'], 'trim'],
			[['organization', 'ur_address', 'inn', 'kpp', 'rs', 'bik'], 'required', 'when' => function ($model) {
				return $model->type == static::TYPE_UR;
			}],
			[['organization'], 'string', 'max' => 255],
			[['ur_address'], 'string', 'max' => 500],
			[['inn', 'kpp', 'rs', 'bik'], 'string', 'max' => 50],

		];
	}

}