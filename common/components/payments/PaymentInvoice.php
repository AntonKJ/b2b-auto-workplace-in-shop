<?php

namespace common\components\payments;

class PaymentInvoice extends PaymentAbstract
{

	public static function getCode(): string
	{
		return 'invoice';
	}

	public function getId(): string
	{
		return static::getCode();
	}

	public function getIdNumber(): int
	{
		return 2;
	}

	public function isActive(): bool
	{
		return true;
	}

	public function getTitle(): string
	{
		return 'Оплата по счёту';
	}

	public function getReserveExtraDays(): int
	{
		return 5;
	}

	public function getIsInvoiceForm(): bool
	{
		return true;
	}


}