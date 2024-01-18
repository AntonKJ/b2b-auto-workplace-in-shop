<?php

namespace api\modules\vendor\modules\goodyear\controllers;

use api\modules\vendor\modules\goodyear\components\Controller;
use api\modules\vendor\modules\goodyear\models\Shop;
use api\modules\vendor\modules\goodyear\Module;
use common\models\query\ZonePriceQuery;
use common\models\Service;
use common\models\ShopStock;
use common\models\ShopStockTotal;
use common\models\TyreGood;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use Exception;
use stdClass;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use function is_array;

class ShopController extends Controller
{

	/**
	 * @var GoodAvailabilityService
	 */
	protected $availabilityService;


	public function __construct(string $id, Module $module, GoodAvailabilityServiceInterface $availabilityService, array $config = [])
	{
		$this->availabilityService = $availabilityService;
		parent::__construct($id, $module, $config);
	}

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
				'view' => ['GET'],
				'goods' => ['GET'],
				'good-by-sku' => ['GET'],
			],
		];

		return $behaviors;
	}

	public function actionIndex()
	{

		$shops = Shop::find()
			->published()
			->defaultOrder()
			->andWhere('network_id != 10')
			->with([
				'servicesRel' => static function (ActiveQuery $q) {
					$q
						->alias('s2s')
						->indexBy('service_id')
						->andWhere([
							's2s.service_id' => [Service::SEASON_STORAGE_SERVICE, Service::TYRE_MOUNT_SERVICE],
						]);
				},
				'timeFrom',
				'timeTo',
				'timeWeekendFrom',
				'timeWeekendTo',
				'region',
			])
			->all();

		return ['response' => ['shops' => $shops]];
	}

	/**
	 * @param $shopId
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function actionView($shopId)
	{

		$shop = Shop::find()
			->published()
			->byId($shopId)
			->andWhere('network_id != 10')
			->with([
				'servicesRel' => static function (ActiveQuery $q) {
					$q
						->alias('s2s')
						->indexBy('service_id')
						->andWhere([
							's2s.service_id' => [Service::SEASON_STORAGE_SERVICE, Service::TYRE_MOUNT_SERVICE],
						]);
				},
				'timeFrom',
				'timeTo',
				'timeWeekendFrom',
				'timeWeekendTo',
				'region',
			])
			->one();

		if ($shop === null)
			throw new NotFoundHttpException('Магазин не найден.');

		return ['response' => ['shop' => $shop]];
	}

	/**
	 * @param $shopId
	 * @return \yii\console\Response|Response
	 * @throws NotFoundHttpException
	 */
	public function actionGoods($shopId)
	{

		if (($shop = Shop::find()->published()->byId($shopId)->andWhere('network_id != 10')->one()) === null) {
			throw new NotFoundHttpException('Магазин не найден.');
		}

		$stockQuery = ShopStockTotal::find()
			->alias('sst')
			->select(['a.idx id', 'a.manuf_code sku', 'IF(ss.amount IS NULL, 0, IF(ss.amount > 20, 20, ss.amount)) amount'])
			->byShopId($shop->getId())
			->innerJoin(TyreGood::tableName() . ' a', 'a.idx = sst.item_idx AND a.prod_code = :brand AND (IFNULL(a.manuf_code, "") != "")', [
				':brand' => 'goodyear',
			])
			->leftJoin(ShopStock::tableName() . ' ss', 'ss.item_idx = sst.item_idx AND ss.shop_id = sst.shop_id');

		$region = $shop->region;

		if (null !== $region) {
			$stockQuery
				->addSelect(['zp.price price'])
				->joinWith(['zonePrice' => static function (ZonePriceQuery $q) use ($region) {
					$q
						->alias('zp')
						->byRegionZonePrice($region);
				}], false);
		}

		$stockQuery->asArray();

		$out = [];

		/**
		 * @var ShopStock $shopStock
		 */
		foreach ($stockQuery->each(1000) as $shopStock) {

			$available = (object)[
				0 => (int)$shopStock['amount'],
			];

			$out[] = [
				'sku' => $shopStock['sku'],
				'price' => (float)$shopStock['price'],
				'availability' => $available,
			];
		}

		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->content = json_encode(['response' => ['stock' => $out]]);

		return $response;
	}

	/**
	 * @param int $shopId
	 * @param string $sku
	 * @return \yii\console\Response|Response
	 * @throws NotFoundHttpException
	 * @throws Exception
	 */
	public function actionGoodBySku($shopId, $sku)
	{

		if (($shop = Shop::find()->published()->byId($shopId)->andWhere('network_id != 10')->one()) === null) {
			throw new NotFoundHttpException('Магазин не найден.');
		}

		// fix: быстрый fix, переключил на таблицу shopStockTotal,
		// т.к. в shopStock фильтрует только по фактическому наличию, без прогноза
		$stockQuery = ShopStockTotal::find()
			->alias('ss')
			->select(['a.idx id', 'a.manuf_code sku'])
			->byShopId($shop->getId())
			->innerJoin(TyreGood::tableName() . ' a', 'a.idx = ss.item_idx AND a.prod_code = :brand AND (IFNULL(a.manuf_code, "") != "")', [
				':brand' => 'goodyear',
			]);

		$stockQuery->andWhere(['a.manuf_code' => $sku]);

		$region = $shop->region;

		if (null !== $region) {
			$stockQuery
				->addSelect(['zp.price price'])
				->joinWith(['zonePrice' => static function (ZonePriceQuery $q) use ($region) {
					$q
						->alias('zp')
						->byRegionZonePrice($region);
				}], false);
		}

		$stockQuery->asArray();

		$out = [];

		/**
		 * @var ShopStock $shopStock
		 */
		foreach ($stockQuery->each(1000) as $shopStock) {

			$available = (object)[];

			if (!empty($sku)) {

				$good = TyreGood::find()->byId($shopStock['id'])->one();
				$availability = $this->availabilityService->getRealAvailability($good->getId(), $shop->zone_id);

				if (
					isset($availability[1]['availability'])
					&& is_array($availability[1]['availability'])
					&& [] !== $availability[1]['availability']
				) {

					$data = [];

					foreach ($availability[1]['availability'] as $shopData) {

						$days = $shopData['days'];
						$qty = $shopData['qty'];

						if (!isset($data[$shopData['shop_id']][(string)$days])) {
							$data[$shopData['shop_id']][(string)$days] = 0;
						}

						$data[$shopData['shop_id']][(string)$days] += $qty;
					}

					foreach (array_keys($data) as $shop_id) {

						ksort($data[$shop_id]);

						$prevIndex = null;
						foreach (array_keys($data[$shop_id]) as $nDay) {

							if (null !== $prevIndex) {
								$data[$shop_id][$nDay] += $data[$shop_id][$prevIndex];
							}

							$prevIndex = $nDay;
						}
					}
					$data = array_map(static function ($v) {
						// Если нету элемента `0`, добавляем
						if (!isset($v['0'])) {
							$v = array_merge(['0' => 0], $v);
						}
						return array_map(static function ($d) {
							return min($d, 20);
						}, $v);
					}, $data);
					$available = isset($data[$shop->getId()]) ? (object)$data[$shop->getId()] : new stdClass();
				}
			}

			$out[] = [
				'sku' => $shopStock['sku'],
				'price' => (float)$shopStock['price'],
				'availability' => $available,
			];
		}

		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->content = json_encode(['response' => ['stock' => $out]]);

		return $response;
	}

}
