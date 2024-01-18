<?php

namespace common\components;

class QuarantineProvider
{

	public const SCHEMA_NO_PICKUPS = 'NO_PICKUPS';
	public const SCHEMA_NO_CASH_PAYMENT = 'SCHEMA_NO_CASH_PAYMENT';

	public static function quarantineSchema(): ?string
	{
		return null;
	}

	public static function isQuarantineModeActive(): bool
	{
		//return false;
		return !empty(static::quarantineSchema());
	}

	public static function getActiveShopIdOptions(): array
	{
		return [
			//19 => [12],// Москва
			//24 => [664, 662],// Питер
			//209 => [641, 694, 693, 668],// Краснодар
			//638 => [698],// Сочи
		];
	}

	public static function isShopActive(int $regionId, int $shopId): bool
	{
		$options = static::getActiveShopIdOptions();
		return !isset($options[$regionId]) || in_array($shopId, $options[$regionId]);
	}

}