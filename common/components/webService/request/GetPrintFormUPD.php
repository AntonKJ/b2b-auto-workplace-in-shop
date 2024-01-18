<?php

namespace common\components\webService\request;

class GetPrintFormUPD extends BaseRequest
{

	const PRINT_TYPE_PDF = 0;
	const PRINT_TYPE_XLSX = 1;

	/**
	 * @var integer
	 */
	public $CheckSumm = 0;

	/**
	 * @var boolean
	 */
	public $PrintType;

	/**
	 * @var string
	 */
	public $OrderNumber;

	static public function getPrintTypeOptions()
	{
		return [
			static::PRINT_TYPE_PDF => 'pdf',
			static::PRINT_TYPE_XLSX => 'xlsx',
		];
	}

	public function getTypeCode()
	{
		$options = static::getPrintTypeOptions();
		return isset($options[$this->PrintType]) ? $options[$this->PrintType] : "неизвестный тип `{$this->PrintType}`";
	}

	public function rules()
	{
		return [

			[['OrderNumber'], 'required'],
			[['OrderNumber'], 'string', 'max' => 16],

			[['PrintType'], 'required'],
			[['PrintType'], 'in', 'range' => array_keys(static::getPrintTypeOptions())],

		];
	}

}