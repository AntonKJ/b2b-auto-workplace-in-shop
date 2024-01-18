<?php

namespace api\modules\vendor\modules\cordiant\controllers;

use api\modules\vendor\modules\cordiant\components\Controller;
use common\models\query\ZonePriceQuery;
use common\models\Region;
use common\models\TyreGood;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\filters\VerbFilter;

class GoodController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{

		$behaviors = parent::behaviors();

		$behaviors['verbs'] = [
			'class' => VerbFilter::class,
			'actions' => [
				'index' => ['GET'],
			],
		];

		return $behaviors;
	}

	/**
	 * @return array
	 */
	public function actionIndex()
	{

		$region = Region::find()
			->byZoneType(Region::ZONE_TYPE_WWW)
			->byId(1)// Москва
			->one();

		$goodQuery = TyreGood::find()
			->alias('g')
			->select([
				'g.idx',
				'g.idx id',
				'g.manuf_code sku',
				'b.url brand_url',
				'm.url model_url',
				new Expression("IF(m.season = :seasonSummer, 'summer', 'winter') season", [':seasonSummer' => 's']),
				'g.sz size',
				'g.in_type li',
				'g.cc sr',
				'g.prod_year year'
			])
			->joinWith([
				'brand' => function (ActiveQuery $q) {
					$q->alias('b');
				},
				'model' => function (ActiveQuery $q) {
					$q->alias('m');
				},
			], false, 'INNER JOIN')
			->andWhere([
				'g.prod_code' => ['cordiant'],
			])
			->asArray();

		if (null !== $region) {

			$goodQuery
				->addSelect(['zp.price price'])
				->joinWith(['zonePrice' => function (ZonePriceQuery $q) use ($region) {
					$q
						->alias('zp')
						->byRegionZonePrice($region);
				}]);
		}

		$funcPrepare = function ($v) {
			return trim(preg_replace(['/[^a-zA-Z0-9_\-]/ui', '(-{2,})'], '-', $v), '-');
		};

		$out = [];

		/**
		 * @var TyreGood $good
		 */
		foreach ($goodQuery->each(1000) as $good) {

			$url = implode('/', [
					'https://www.myexample.ru',
					'catalog',
					$good['brand_url'],
					$good['season'],
					$good['model_url'],
					$funcPrepare($good['size']),
					$funcPrepare($good['li'] . $good['sr']),
				]) . '/';

			$og = [
				'sku' => $good['sku'],
				'price' => (float)$good['price'],
				'url' => $url,
			];

			if ( strlen($good['year']) > 3 ) {
				$og['year'] = $good['year'];
			}

			$out[] = $og;
		}

		return $out;
	}

}