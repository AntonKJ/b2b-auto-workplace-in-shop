<?php

namespace api\modules\regular\components;

use api\models\VendorOrder;
use yii\base\Component;

class Order extends Component
{

	public const STATUS_ERROR = 'ERROR';
	public const STATUS_IN_RESERVE = 'RESERVED';
	public const STATUS_COMPLETED = 'COMPLETED';
	public const STATUS_CANCELED = 'CANCELED';

	/**
	 * @return array
	 */
	public static function getOrderStatusOptions(): array
	{
		return [
			static::STATUS_ERROR => VendorOrder::STATUS_CANCELLED,
			static::STATUS_IN_RESERVE => VendorOrder::STATUS_IN_RESERVE,
			static::STATUS_COMPLETED => VendorOrder::STATUS_COMPLETED,
			static::STATUS_CANCELED => VendorOrder::STATUS_CANCELLED,
		];
	}

}
