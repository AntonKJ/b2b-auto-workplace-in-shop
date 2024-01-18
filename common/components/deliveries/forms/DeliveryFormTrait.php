<?php

namespace common\components\deliveries\forms;

use common\models\Shop;

//todo отказаться от этого трейта (быстрый фикс)

/**
 * Trait DeliveryFormTrait
 * @package common\components\deliveries\forms
 */
trait DeliveryFormTrait
{

	protected $_shop = false;

	/**
	 * @param bool $refresh
	 * @return Shop|null
	 */
	public function getShop($refresh = false)
	{

		if ($this->_shop === false || $refresh)
			$this->_shop = Shop::find()->byId($this->getShopId())->one();

		return $this->_shop;
	}


	/**
	 * @return \DateTime
	 */
	public function getDateAsDateTime(): \DateTime
	{
		$date = (new \DateTime($this->date))
			->setTimezone(new \DateTimeZone(\Yii::$app->timeZone));

		return $date;
	}

}