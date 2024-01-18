<?php

namespace api\modules\vendor\modules\goodyear\controllers;

use api\modules\vendor\modules\goodyear\components\Controller;
use common\models\TyreGood;
use yii\db\Expression;
use yii\db\Query;
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

		$goodQuery = TyreGood::find()
			->alias('g')
			->select([
				'g.idx id',
				'g.manuf_code sku',
				'b.url brand_url',
				'm.url model_url',
				new Expression("IF(m.season = :seasonSummer, 'summer', 'winter') season", [':seasonSummer' => 's']),
				'g.sz size',
				'g.in_type li',
				'g.cc sr',
			])
			->joinWith([
				'brand' => static function (Query $q) {
					$q->alias('b');
				},
				'model' => static function (Query $q) {
					$q->alias('m');
				},
			], false, 'INNER JOIN')
			->andWhere('g.prod_code = :brand', [':brand' => 'goodyear'])
			->asArray();

		$funcPrepare = static function ($v) {
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

			$out[] = [
				'sku' => $good['sku'],
				'url' => $url,
			];
		}

		return ['response' => ['items' => $out]];
	}

}
