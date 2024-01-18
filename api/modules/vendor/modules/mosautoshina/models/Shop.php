<?php

namespace api\modules\vendor\modules\mosautoshina\models;

use common\models\Service;
use common\models\Shop as ShopBase;
use domain\entities\GeoPosition;

class Shop extends ShopBase
{

	public function getSchedule(): array
	{

		$data = [];

		$days = [
			'пн' => false,
			'вт' => false,
			'ср' => false,
			'чт' => false,
			'пт' => false,
			'сб' => true,
			'вс' => true,
		];

		foreach ($days as $day => $isWeekend) {

			$timeFrom = $isWeekend ? $this->timeWeekendFrom : $this->timeFrom;
			$timeTo = $isWeekend ? $this->timeWeekendTo : $this->timeTo;

			$work = $timeFrom !== null && $timeTo !== null;

			$data[] = [
				'dayOfWeek' => $day,
				'isWorking' => $work,
				'startHour' => $work ? $timeFrom->hours : null,
				'startMinute' => $work ? $timeFrom->minutes : null,
				'endHour' => $work ? $timeTo->hours : null,
				'endMinute' => $work ? $timeTo->minutes : null,
			];
		}

		return $data;
	}

	public function getPromotions()
	{
		return [];
	}

	public function getTyreService()
	{
		return isset($this->servicesRel[Service::TYRE_MOUNT_SERVICE]);
	}

	public function getSeasonService()
	{
		return isset($this->servicesRel[Service::SEASON_STORAGE_SERVICE]);
	}

	public function getPaymentMethods()
	{
		$paymentMethods = ['CASH', 'NOT_CASH'];

		if ($this->getIsPaymentByCards())
			$paymentMethods[] = 'BANK_CARD';

		return $paymentMethods;
	}

	public function getPhoneFormatted()
	{
		$phone = trim($this->phone);
		return empty($phone) ? null : $phone;
	}

	public function fields()
	{

		$fields = [

			'id',

			'schedule' => 'schedule',

			'phone' => 'phoneFormatted',

			'address' => function ($model) {
				return implode(' ', [$model->location, $this->address_brief]);
			},

			'numberOfPosts' => 'num_lines',

			'name' => 'title',

			'promotions',

			'gps' => function (self $model) {

				$position = $model->getGeoPosition();
				return $position instanceof GeoPosition ? $position->toArray() : null;
			},

			'tireService' => 'tyreService',

			'seasonService',

			'paymentMethods',

			'url' => 'urlAbsolute'

		];

		return $fields;
	}

	public function getUrlAbsolute(): string
	{
		return "https://{$this->region->url_frag}.myexample.ru/{$this->url}/";
	}

	public function extraFields()
	{
		return [];
	}

}
