<?php

namespace api\models\query;

use api\models\VendorOrder;

class VendorOrderQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param string|array $vendor
	 * @return $this
	 */
	public function byVendor($vendor)
	{
		return $this->andWhere([
			'vendor' => $vendor,
		]);
	}

	/**
	 * @return $this
	 */
	public function byNotNotified()
	{
		return $this->andWhere('notified_status IS NULL OR status != notified_status');
	}

	/**
	 * @param int $maxAttempts
	 * @return $this
	 */
	public function byMaxAttempts(int $maxAttempts): self
	{
		return $this->andWhere('attempts < :maxAttempts', [':maxAttempts' => $maxAttempts]);
	}

	/**
	 * @param $status
	 * @return $this
	 */
	public function byStatus($status)
	{
		return $this->andWhere([
			'status' => $status,
		]);
	}

	/**
	 * @param $orderId
	 * @return $this
	 */
	public function byOrderId($orderId)
	{
		return $this->andWhere([
			'order_id' => $orderId,
		]);
	}

	/**
	 * @inheritdoc
	 * @return VendorOrder[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return VendorOrder|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
