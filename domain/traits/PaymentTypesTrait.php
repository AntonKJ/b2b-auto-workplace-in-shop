<?php

namespace domain\traits;

use common\components\payments\PaymentCash;
use common\components\payments\PaymentInvoice;
use domain\interfaces\PaymentTypesInterface;
use yii\base\InvalidCallException;

/**
 * @package domain\traits
 */
trait PaymentTypesTrait
{

	static public function getPaymentTypeOptions(): array
	{
		return [
			PaymentTypesInterface::TYPE_CACHE => PaymentCash::getCode(),
			PaymentTypesInterface::TYPE_INVOICE => PaymentInvoice::getCode(),
		];
	}

	public function getPaymentTypeMask(): int
	{
		throw new InvalidCallException('PaymentTypesTrait::getPaymentTypeMask must be defined!');
	}

	/**
	 * @inheritdoc
	 */
	public function isPaymentTypeSet($type): bool
	{
		return (($this->getPaymentTypeMask() & $type) == $type);
	}

	/**
	 * @inheritdoc
	 */
	public function getPaymentTypes(): array
	{

		$types = array_filter(static::getPaymentTypeOptions(), function ($v, $k) {
			return $this->isPaymentTypeSet($k);
		}, ARRAY_FILTER_USE_BOTH);

		return $types;
	}

}