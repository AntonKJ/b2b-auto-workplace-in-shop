<?php

namespace api\modules\vendor\modules\goodyear\models;

use common\models\Region as RegionBase;
use domain\entities\GeoPosition;

class Region extends RegionBase
{

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'id',
			'name' => 'title',
			'gps' => static function (self $model) {
				/** @var GeoPosition $gpos */
				if (($gpos = $model->getGeoPosition()) === null) {
					return null;
				}
				return implode(',', (array)$gpos);
			},
			'payment-methods' => static function ($model) {
				return [
					'CASH',
				];
			},
			'deliveryPrice1' => static function ($model) {
				return 0.0;
			},
			'deliveryPrice2' => static function ($model) {
				return 0.0;
			},
			'deliveryPrice3' => static function ($model) {
				return 0.0;
			},
			'deliveryPrice4' => static function ($model) {
				return 0.0;
			},
		];
	}

}
