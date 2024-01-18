<?php

namespace common\components\deliveries\forms;

use common\components\payments\PaymentInterface;
use yii\helpers\ArrayHelper;

/**
 * Trait PaymentFormTrait
 * @package common\components\deliveries\forms
 */
trait PaymentFormTrait
{

	/**
	 * @return PaymentInterface
	 */
	public function getPaymentModel()
	{
		return ArrayHelper::getValue($this->_payments, $this->payment, null);
	}

}