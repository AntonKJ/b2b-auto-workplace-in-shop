<?php

namespace common\components\webService\request;

class GetOrdersBySearchKey extends BaseRequest
{

	const SEARCH_KEY_PHONE = 0;
	const SEARCH_KEY_INVOICE = 1;

	/**
	 * @var string
	 */
	public $SearchKey;

	/**
	 * @var string
	 */
	public $SearchValue;

	/**
	 * @var string|null
	 */
	public $ClientCode;

	static public function getSearchKeyOptions()
	{
		return [
			static::SEARCH_KEY_PHONE => 'phone',
			static::SEARCH_KEY_INVOICE => 'invoice',
		];
	}

	public function rules()
	{
		return [

			[['SearchKey'], 'trim'],
			[['SearchKey'], 'required'],
			[['SearchKey'], 'in', 'range' => array_keys(static::getSearchKeyOptions())],

			[['SearchValue'], 'trim'],

			[['SearchValue'], 'filter', 'filter' => function ($v) {
				return preg_replace('/[^\d]/ui', '', $v);
			}, 'when' => function (GetOrdersBySearchKey $model) {
				return $this->SearchKey == static::SEARCH_KEY_PHONE;
			}],

			[['SearchValue'], 'required'],

			[['SearchValue'], 'string', 'length' => 10, 'when' => function (GetOrdersBySearchKey $model) {
				return $this->SearchKey == static::SEARCH_KEY_PHONE;
			}],

			[['SearchValue'], 'string', 'min' => 4, 'when' => function (GetOrdersBySearchKey $model) {
				return $this->SearchKey == static::SEARCH_KEY_INVOICE;
			}],

		];
	}

}