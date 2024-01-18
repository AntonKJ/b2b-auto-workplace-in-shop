<?php

namespace common\components\deliveries;

use common\components\deliveries\forms\DeliveryFormInterface;
use yii\base\Arrayable;

interface DeliveryInterface extends Arrayable
{

	public function getRegion();

	public function getUser();

	public function isActive(): bool;

	public function getTitle(): string;

	public function getData();

	/**
	 * Подготовка данных для отправки на клиент
	 * @return mixed
	 */
	public function getDataForClient();

	public function getPayments();

	/**
	 * @return DeliveryFormInterface
	 */
	public function getFormModel();

	public function getActiveOrderTypes(): array;

	static public function getCategory(): string;

}