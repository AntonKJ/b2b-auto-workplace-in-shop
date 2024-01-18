<?php

namespace common\components\payments;

class PaymentCash extends PaymentAbstract
{

	public static function getCode(): string
	{
		return 'cash';
	}

	public function getId(): string
	{
		return static::getCode();
	}

	public function getIdNumber(): int
	{
		return 1;
	}

	public function isActive(): bool
	{
		return true;
	}

	public function getTitle(): string
	{
		return 'Оплата наличными';
	}

	public function getIsInvoiceForm(): bool
	{
		return false;
	}

}